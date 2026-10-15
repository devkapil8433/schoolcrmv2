<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tailwind utility class strings for the student portal (no portal-* component classes).
 * Used by layout components; views should inline these utilities directly.
 */
if (!function_exists('portal_tw_btn')) {
    function portal_tw_btn($variant = 'primary', $size = '')
    {
        $base = 'inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none';
        $variants = array(
            'primary' => 'bg-primary text-white hover:bg-primary-700 focus:ring-primary',
            'secondary' => 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 focus:ring-slate-400',
            'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        );
        $classes = $base . ' ' . (isset($variants[$variant]) ? $variants[$variant] : $variants['primary']);
        if ($size === 'sm') {
            $classes .= ' px-3 py-1.5 text-xs';
        }
        return $classes;
    }
}

if (!function_exists('portal_tw_classes')) {
    function portal_tw_classes($key)
    {
        static $map = null;
        if ($map === null) {
            $map = array(
                'card' => 'bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden',
                'card_header' => 'px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3',
                'card_body' => 'p-5',
                'content' => 'space-y-6',
                'page_header' => 'mb-2 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between',
                'page_title' => 'text-2xl font-semibold text-slate-900 tracking-tight',
                'nav_link' => 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-300 transition-colors hover:bg-slate-800 hover:text-white',
                'nav_link_active' => 'bg-slate-800 text-white border-l-4 border-primary pl-2',
                'nav_child' => 'ml-4 mt-1 space-y-0.5 border-l border-slate-700 pl-3',
                'tabs' => 'flex flex-wrap gap-1 border-b border-slate-200 mb-4',
                'tab' => 'inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-slate-500 border-b-2 border-transparent -mb-px transition-colors hover:text-slate-700',
                'tab_active' => 'text-primary border-primary',
                'table_wrap' => 'overflow-x-auto rounded-lg border border-slate-200',
                'table' => 'w-full text-sm text-left [&_thead]:bg-slate-50 [&_thead]:text-slate-600 [&_thead]:uppercase [&_thead]:text-xs [&_thead]:tracking-wider [&_th]:px-4 [&_th]:py-3 [&_th]:font-semibold [&_th]:border-b [&_th]:border-slate-200 [&_th]:whitespace-nowrap [&_td]:px-4 [&_td]:py-3 [&_td]:border-b [&_td]:border-slate-100 [&_tbody_tr:hover]:bg-slate-50',
                'label' => 'block text-sm font-medium text-slate-700 mb-1.5',
                'input' => 'block w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-primary focus:ring-primary',
                'empty' => 'flex flex-col items-center justify-center py-12 px-4 text-center',
                'empty_icon' => 'w-12 h-12 text-slate-300 mb-3',
                'empty_text' => 'text-slate-500 text-sm',
                'detail_grid' => 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-x-6 gap-y-5 [&_dt]:text-xs [&_dt]:font-medium [&_dt]:uppercase [&_dt]:tracking-wide [&_dt]:text-slate-400 [&_dt]:mb-1 [&_dd]:text-sm [&_dd]:font-semibold [&_dd]:text-slate-900 [&_dd]:m-0',
                'badge' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold',
                'badge_success' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700',
                'badge_danger' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-700',
                'badge_warning' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700',
                'badge_info' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-100 text-sky-700',
                'badge_primary' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-50 text-primary',
                'dropdown' => 'absolute right-0 mt-2 w-56 rounded-xl bg-white shadow-lg ring-1 ring-slate-200 py-1 z-50 hidden',
                'dropdown_wide' => 'w-72',
                'dropdown_item' => 'flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors',
                'modal' => 'bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-hidden flex flex-col mx-auto',
                'modal_lg' => 'max-w-4xl',
                'modal_full' => 'max-w-6xl w-full',
            );
        }
        return isset($map[$key]) ? $map[$key] : '';
    }
}
