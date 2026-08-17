<?php

namespace App\Support;

class DatatableHtml
{
    public static function statusBadge(int $id, bool $status): string
    {
        return $status
            ? "<span onclick='update_status({$id},0)' id='span_{$id}' class='label label-success' style='cursor:pointer'>Active </span>"
            : "<span onclick='update_status({$id},1)' id='span_{$id}' class='label label-danger' style='cursor:pointer'> Inactive </span>";
    }

    public static function checkbox(int $id, bool $disabled = false): string
    {
        $disabledAttr = $disabled ? 'disabled' : '';

        return "<input type=\"checkbox\" name=\"checkbox[]\" {$disabledAttr} value={$id} class=\"checkbox column_checkbox\" >";
    }

    /**
     * @param  array<int, array{label: string, icon: string, url?: string, onclick?: string, target?: string, can: bool}>  $actions
     */
    public static function actionMenu(array $actions): string
    {
        $items = '';

        foreach ($actions as $action) {
            if (! $action['can']) {
                continue;
            }

            $target = isset($action['target']) ? ' target="'.$action['target'].'"' : '';
            $href = isset($action['url']) ? 'href="'.$action['url'].'"' : 'style="cursor:pointer"';
            $onclick = isset($action['onclick']) ? ' onclick="'.$action['onclick'].'"' : '';

            $items .= '<li><a title="'.$action['label'].'" '.$href.$onclick.$target.'>'
                .'<i class="fa fa-fw '.$action['icon'].'"></i>'.$action['label']
                .'</a></li>';
        }

        return '<div class="btn-group" title="View Account">'
            .'<a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">'
            .'Action <span class="caret"></span></a>'
            .'<ul role="menu" class="dropdown-menu dropdown-light pull-right">'.$items.'</ul>'
            .'</div>';
    }
}
