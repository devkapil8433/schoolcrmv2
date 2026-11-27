(function(window, $) {
    'use strict';

    window.isPortalLayout = function() {
        return !!document.getElementById('portal-app');
    };

    window.getPortalDataTableDom = function() {
        return '<"portal-dt-toolbar flex flex-wrap items-center justify-between gap-3 mb-4"Bf>rt<"portal-dt-footer flex flex-wrap items-center justify-between gap-3 mt-4 pt-4 border-t border-slate-200"<"portal-dt-meta flex flex-wrap items-center gap-3"li>p>';
    };

    window.getPortalDataTableDomSimple = function() {
        return '<"portal-dt-toolbar flex flex-wrap items-center justify-between gap-3 mb-4"f>rt<"portal-dt-footer flex flex-wrap items-center justify-between gap-3 mt-4 pt-4 border-t border-slate-200"<"portal-dt-meta flex flex-wrap items-center gap-3"li>p>';
    };

    window.getLegacyDataTableDom = function() {
        return '<"row"<"col-sm-6 mb-xs"B><"col-sm-6"f>><"table-responsive"tr><"row"<"col-sm-6 mb-xs"il><"col-sm-6"p>>';
    };

    window.getDataTableDom = function() {
        return window.isPortalLayout() ? window.getPortalDataTableDom() : window.getLegacyDataTableDom();
    };

    if (!$ || !$.fn.dataTable) {
        return;
    }

    function getExportTitle() {
        var $title = $('.export_title').first();
        return $title.length ? $title.html() : '';
    }

    function getExportButtons() {
        return [
            {
                extend: 'copyHtml5',
                text: '<i class="far fa-copy"></i>',
                titleAttr: 'Copy',
                title: getExportTitle,
                exportOptions: {
                    columns: ['thead th:not(.no-export,:last-child)', 'th.isExport']
                }
            },
            {
                extend: 'excelHtml5',
                text: '<i class="fa fa-file-excel"></i>',
                titleAttr: 'Excel',
                title: getExportTitle,
                exportOptions: {
                    columns: ['thead th:not(.no-export,:last-child)', 'th.isExport']
                }
            },
            {
                extend: 'csvHtml5',
                text: '<i class="fa fa-file-alt"></i>',
                titleAttr: 'CSV',
                title: getExportTitle,
                exportOptions: {
                    columns: ['thead th:not(.no-export,:last-child)', 'th.isExport']
                }
            },
            {
                extend: 'pdfHtml5',
                orientation: 'landscape',
                pageSize: 'A4',
                text: '<i class="fa fa-file-pdf"></i>',
                titleAttr: 'PDF',
                title: getExportTitle,
                footer: true,
                customize: function(win) {
                    win.styles.tableHeader.fontSize = 11;
                    win.styles.tableFooter.fontSize = 11;
                    win.styles.tableHeader.alignment = 'left';
                },
                exportOptions: {
                    columns: ['thead th:not(.no-export,:last-child)', 'th.isExport']
                }
            },
            {
                extend: 'print',
                text: '<i class="fa fa-print"></i>',
                titleAttr: 'Print',
                title: getExportTitle,
                customize: function(win) {
                    $(win.document.body).css('font-size', '9pt');
                    $(win.document.body).find('table').addClass('compact').css('font-size', 'inherit');
                    $(win.document.body).find('h1').css('font-size', '14pt');
                },
                footer: true,
                exportOptions: {
                    stripHtml: false,
                    columns: ['thead th:not(.no-export,:last-child)', 'th.isExport']
                }
            },
            {
                extend: 'colvis',
                text: '<i class="fas fa-columns"></i>',
                titleAttr: 'Columns',
                title: getExportTitle,
                postfixButtons: ['colvisRestore']
            }
        ];
    }

    function fixPortalTableLayout(settings) {
        if (!document.getElementById('portal-app')) {
            return;
        }

        var $table = $(settings.nTable);
        $table.addClass('w-full text-sm text-left');

        var $wrapper = $table.closest('.dataTables_wrapper');
        $wrapper.addClass('w-full');

        var $scrollWrap = $wrapper.parent('.overflow-x-auto');
        if ($scrollWrap.length) {
            $scrollWrap.replaceWith($wrapper);
        }

        if (!$table.parent().hasClass('portal-dt-scroll')) {
            $table.wrap('<div class="portal-dt-scroll overflow-x-auto rounded-lg border border-slate-200"></div>');
        }

        $wrapper.find('.table-responsive').first().css({ border: 'none', overflow: 'visible' });

        if ($.fn.DataTable.isDataTable($table[0])) {
            $table.DataTable().columns.adjust();
        }
    }

    $(document).on('init.dt', function(e, settings) {
        fixPortalTableLayout(settings);
    });

    $(document).on('draw.dt', function(e, settings) {
        if (!document.getElementById('portal-app')) {
            return;
        }
        var api = new $.fn.dataTable.Api(settings);
        api.columns.adjust();
    });

    window.initPortalDataTables = function() {
        if (!window.isPortalLayout()) {
            return;
        }

        $('.table_default').each(function() {
            if ($.fn.DataTable.isDataTable(this)) {
                return;
            }
            $(this).DataTable({
                dom: window.getPortalDataTableDomSimple(),
                pageLength: 25,
                ordering: false
            });
        });

        $('.table-export').each(function() {
            if ($.fn.DataTable.isDataTable(this)) {
                return;
            }
            $(this).DataTable({
                dom: window.getPortalDataTableDom(),
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                order: [],
                pageLength: 25,
                columnDefs: [
                    { targets: 'no-sort', orderable: false },
                    { targets: [-1], orderable: false }
                ],
                buttons: getExportButtons()
            });
        });
    };

    $(function() {
        if (window.isPortalLayout()) {
            $.extend(true, $.fn.dataTable.defaults, {
                language: {
                    search: '',
                    searchPlaceholder: 'Search…',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    infoEmpty: 'No entries',
                    infoFiltered: '(filtered from _MAX_ total)',
                    paginate: { previous: 'Prev', next: 'Next' },
                    processing: 'Loading…',
                    zeroRecords: 'No matching records found'
                },
                fnInitComplete: function(settings) {
                    var $search = $('.dataTables_filter input', settings.nTableWrapper);
                    $search.attr('placeholder', 'Search…');
                }
            });
        }
        window.initPortalDataTables();
    });
})(window, window.jQuery);
