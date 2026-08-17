@forelse ($holds as $h)
    <tr>
        <td>{{ $h->id }}</td>
        <td>{{ show_date($h->sales_date) }}</td>
        <td>{{ $h->reference_id }}</td>
        <td>
            <a class="fa fa-fw fa-trash-o text-red" style="cursor: pointer;font-size: 20px;" onclick="hold_invoice_delete({{ $h->id }})" title="Delete Invoice?"></a>
            <a class="fa fa-fw fa-edit text-success" style="cursor: pointer;font-size: 20px;" onclick="hold_invoice_edit({{ $h->id }})" title="Edit Invoice?"></a>
        </td>
    </tr>
@empty
    <tr><td colspan="4" class="text-danger text-center">No Records Found</td></tr>
@endforelse
