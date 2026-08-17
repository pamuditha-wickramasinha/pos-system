<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\Process\Process;

class UserController extends Controller
{
    public function create(): View
    {
        $this->authorize('users_add');

        return view('user.form', ['page_title' => 'Create User']);
    }

    public function view(): View
    {
        $this->authorize('users_view');

        return view('user.list', [
            'page_title' => 'Users List',
            'users' => User::with('roles')->orderBy('id')->get(),
        ]);
    }

    public function edit(User $user): View
    {
        $this->authorize('users_edit');

        return view('user.form', [
            'page_title' => 'Edit User',
            'q_id' => $user->id,
            'username' => $user->username,
            'mobile' => $user->mobile,
            'email' => $user->email,
            'role_id' => $user->roles->first()?->id,
            'profile_picture' => $user->profile_picture,
        ]);
    }

    public function saveOrUpdate(Request $request)
    {
        $isUpdate = $request->input('command') === 'update';

        $rules = [
            'mobile' => 'required',
            'email' => 'required|email',
        ];

        if (! $isUpdate) {
            $rules['pass'] = 'required|min:5|max:12';
            $rules['new_user'] = 'required|min:5|max:12';
            $rules['role_id'] = 'required';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response($validator->errors()->first());
        }

        $profilePicture = null;
        if ($request->hasFile('profile_picture')) {
            $profilePicture = $request->file('profile_picture')->store('users', 'public');
        }

        if ($isUpdate) {
            $id = $request->input('q_id');

            if ((int) $id === 1) {
                return response('Restricted! Can\'t Update User Admin!!');
            }

            if ($request->filled('new_user') && User::where('username', $request->input('new_user'))->where('id', '!=', $id)->exists()) {
                return response('This username already exist.');
            }
            if (User::where('mobile', $request->input('mobile'))->where('id', '!=', $id)->exists()) {
                return response('This Moble Number already exist.');
            }
            if (User::where('email', $request->input('email'))->where('id', '!=', $id)->exists()) {
                return response('This Email ID already exist.');
            }

            $user = User::findOrFail($id);
            $data = [
                'mobile' => $request->input('mobile'),
                'email' => $request->input('email'),
            ];
            if ($request->filled('new_user')) {
                $data['username'] = $request->input('new_user');
            }
            if ($profilePicture) {
                $data['profile_picture'] = $profilePicture;
            }
            if ($request->filled('newpass') && $request->input('newpass') === $request->input('confirm')) {
                $data['password'] = Hash::make($request->input('newpass'));
                $data['legacy_password'] = null;
            }
            $user->update($data);

            if ($request->filled('role_id')) {
                $role = Role::find($request->input('role_id'));
                if ($role) {
                    $user->syncRoles([$role]);
                }
            }

            session()->flash('success', 'Success!! User Updated Succssfully!!');

            return response('success');
        }

        if (User::where('username', $request->input('new_user'))->exists()) {
            return response('This username already exist.');
        }
        if (User::where('mobile', $request->input('mobile'))->exists()) {
            return response('This Moble Number already exist.');
        }
        if (User::where('email', $request->input('email'))->exists()) {
            return response('This Email ID already exist.');
        }

        $user = User::create([
            'username' => $request->input('new_user'),
            'password' => Hash::make($request->input('pass')),
            'mobile' => $request->input('mobile'),
            'email' => $request->input('email'),
            'created_by' => $request->user()->username,
            'profile_picture' => $profilePicture,
            'status' => true,
        ]);

        $role = Role::find($request->input('role_id'));
        if ($role) {
            $user->assignRole($role);
        }

        session()->flash('success', 'Success!! New User created Succssfully!!');

        return response('success');
    }

    public function statusUpdate(Request $request)
    {
        $this->authorize('users_edit');

        User::whereKey($request->input('id'))->update(['status' => $request->input('status')]);

        return response('success');
    }

    public function passwordReset(): View
    {
        return view('user.password-reset', ['page_title' => 'Change Password']);
    }

    public function passwordUpdate(Request $request)
    {
        $user = $request->user();
        $currentPassword = (string) $request->input('currentpass');
        $newPassword = (string) $request->input('newpass');

        $valid = Hash::check($currentPassword, $user->password)
            || ($user->legacy_password && hash_equals($user->legacy_password, md5($currentPassword)));

        if (! $valid) {
            return response('Invalid Current Password!');
        }

        $user->forceFill([
            'password' => Hash::make($newPassword),
            'legacy_password' => null,
        ])->save();

        return response('success');
    }

    public function destroy(Request $request)
    {
        $this->authorize('users_delete');

        $id = (int) $request->input('q_id');

        if ($id === 1) {
            return response("Restricted! Can't Delete User Admin!!");
        }

        User::whereKey($id)->delete();

        return response('success');
    }

    public function dbBackup(Request $request)
    {
        $this->authorize('database_backup');

        $config = config('database.connections.'.config('database.default'));
        $timestamp = now()->format('d-m-Y-h-i-s');
        $sqlPath = storage_path("app/dbbackup-{$timestamp}.sql");
        $zipPath = storage_path("app/dbbackup{$timestamp}.zip");

        $mysqldump = $this->findMysqldump();

        $command = [
            $mysqldump,
            '--protocol=TCP',
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--user='.$config['username'],
            '--result-file='.$sqlPath,
            $config['database'],
        ];

        if (! empty($config['password'])) {
            $command[] = '--password='.$config['password'];
        }

        $process = new Process($command);
        $process->run();

        if (! $process->isSuccessful() || ! file_exists($sqlPath)) {
            report(new \RuntimeException('Database backup failed: '.$process->getErrorOutput()));

            return response('Backup Failed! Please check server configuration.', 500);
        }

        $zip = new \ZipArchive;
        $zip->open($zipPath, \ZipArchive::CREATE);
        $zip->addFile($sqlPath, "dbbackup{$timestamp}.sql");
        $zip->close();

        unlink($sqlPath);

        return response()->download($zipPath, "dbbackup{$timestamp}.zip")->deleteFileAfterSend(true);
    }

    protected function findMysqldump(): string
    {
        $candidates = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'mysqldump',
        ];

        foreach ($candidates as $candidate) {
            if ($candidate === 'mysqldump' || file_exists($candidate)) {
                return $candidate;
            }
        }

        return 'mysqldump';
    }
}
