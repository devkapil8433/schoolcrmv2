(function ($) {
    'use strict';
    $(document).ready(function () {
        initOnlineExamEditors();
        initImageDatabasePage();

        $('.chk-sendsmsmail').on('change', function () {
            if ($(this).is(':checked')) {
                $(this).parents('.form-group').find('select > option').prop("selected", "selected");
                $(this).parents('.form-group').find('select').trigger("change");
            } else {
                $(this).parents('.form-group').find('select').val(null).trigger('change');
            }
        });

        $("#examType").on("change", function (ev) {
            if ($(this).val() == 0) {
                $("#examFee").hide("slow");
            } else {
                $("#examFee").show("slow");
            }
        });
    });
})(jQuery);

// exam duration update
function durationUpdate() {
    function zeroPad(number) {
        return (number < 10 ? '0' : '') + number;
    }
    splitTime = examDuration.split(':');
    var hours = parseInt(splitTime[0], 10);
    var minutes = parseInt(splitTime[1], 10);
    var seconds = parseInt(splitTime[2], 10);
    --seconds;
    minutes = (seconds < 0) ? --minutes : minutes;
    seconds = (seconds < 0) ? 59 : seconds;
    hours = (minutes < 0) ? --hours : hours;
    minutes = (minutes < 0) ? 59 : minutes;

    hours = zeroPad(hours);
    minutes = zeroPad(minutes);
    seconds = zeroPad(seconds);
    if (hours < 0)
        clearInterval(interval);

    if ((seconds <= 0) && (minutes <= 0) && (hours <= 0)) {
        clearInterval(interval);
        $('#answerForm').submit();
    }

    examDuration = hours + ":" + minutes + ":" + seconds;
    var r = hours + ":" + minutes + ":" + seconds;
    return r;
}

function clearAnswer() {
    $.each($('.step-pane.active input'), function () {
        elementType = $(this).attr('type');
        if (elementType == 'radio' || elementType == 'checkbox') {
            $(this).prop('checked', false);
        } else if (elementType == 'text') {
            $(this).val('');
        }
    });
}

function makeAnswered(questionID) {
    var answer = 0;
    $.each($('#answerForm #step' + questionID + ' input'), function () {
        elementType = $(this).attr('type');
        if (elementType == 'radio' || elementType == 'checkbox') {
            if ($(this).prop('checked')) {
                answer = 1;
                return answer;
            }
        } else if (elementType == 'text') {
            if ($(this).val() != '') {
                answer = 1;
                return answer;
            }
        }
    });
    if (answer == 1) {
        $('#question' + questionID).html('<i class="fas fa-check"></i>');
    } else {
        $('#question' + questionID).html(questionID);
    }
}

function getExercise(id) {
    $.ajax({
        url: base_url + 'onlineexam/exerciseDetails',
        type: 'POST',
        data: { 'id': id },
        dataType: "json",
        success: function (data) {
            if (!data || !data.id) {
                return;
            }
            $('#eexercise_id').val(data.id);
            $('#eexercise_name').val(data.name);
            var classId = data.class_id ? String(data.class_id) : '';
            var subjectId = data.subject_id ? String(data.subject_id) : '';

            function setSubjectAndOpen() {
                $.post(base_url + 'onlineexam/getByClass', { classID: classId }, function (html) {
                    $('#esubject_id').html('<option value="">' + (typeof translate_select !== 'undefined' ? translate_select : 'Select') + '</option>' + html);
                    if (subjectId) {
                        $('#esubject_id').val(subjectId);
                    }
                    $('#esubject_id').trigger('change');
                    mfp_modal('#modal');
                }).fail(function () {
                    mfp_modal('#modal');
                });
            }

            function setClassThenSubject() {
                if (!classId) {
                    mfp_modal('#modal');
                    return;
                }
                if ($('#eclass_id option[value="' + classId + '"]').length) {
                    $('#eclass_id').val(classId).trigger('change.select2');
                    setSubjectAndOpen();
                } else {
                    $('#eclass_id').val(classId).trigger('change');
                    setSubjectAndOpen();
                }
            }

            if ($('#ebranch_id').length) {
                var branchId = data.branch_id ? String(data.branch_id) : '';
                $('#ebranch_id').val(branchId).trigger('change.select2');
                $.post(base_url + 'ajax/getClassByBranch', { branch_id: branchId }, function (html) {
                    $('#eclass_id').html(html);
                    setClassThenSubject();
                }).fail(function () {
                    mfp_modal('#modal');
                });
            } else {
                setClassThenSubject();
            }
        }
    });
}

function getQuestion(id, elem) {
    var btn = $(elem);
    $.ajax({
        url: base_url + 'onlineexam/getQuestion',
        type: 'POST',
        data: { 'id': id },
        dataType: "html",
        beforeSend: function () {
            btn.button('loading');
        },
        success: function (data) {
            $('#quick_view').html(data);
            mfp_modal('#modal');
        },
        error: function (xhr) {
            btn.button('reset');
        },
        complete: function () {
            btn.button('reset');
            btn.tooltip("hide");
        }
    });
}

$("#examType").on("change", function () {
    if (this.value == 1) {
        $("#examFee").show("slow");
    } else {
        $("#examFee").hide("slow");
    }
});

$(document).on("click", ".exam-status", function () {
    var state = $(this).prop('checked');
    var id = $(this).data('id');
    if (state != null) {
        $.ajax({
            type: 'POST',
            url: base_url + "onlineexam/exam_status",
            data: {
                id: id,
                status: state
            },
            dataType: "json",
            success: function (data) {
                if (data.status == true) {
                    swal({
                        title: "Successfully",
                        text: data.msg,
                        buttonsStyling: false,
                        showCloseButton: true,
                        focusConfirm: false,
                        confirmButtonClass: "btn btn-default swal2-btn-default",
                        type: "success"
                        }).then((result) => {
                            if (result.value) {
                               cusDataTable.ajax.reload( null, false);
                            }
                        }
                    );
                }
            }
        });
    }
});

function getExamByClass(class_id) {
    $.ajax({
        url: base_url + 'onlineexam/getExamByClass',
        type: 'POST',
        data: { class_id: class_id },
        success: function (data) {
            $('#examID').html(data);
        }
    });
}

function getStudentResult(id) {
    $.ajax({
        url: base_url + 'userrole/getStudent_result',
        type: 'POST',
        data: { 'id': id },
        dataType: "html",
        success: function (data) {
            $('#quick_view').html(data);
            mfp_modal('#modal');
        }
    });
}

function getAdminStudentResult(examID, studentID) {
    $.ajax({
        url: base_url + 'onlineexam/getStudent_result',
        type: 'POST',
        data: { 'examID': examID, 'studentID': studentID },
        dataType: "html",
        success: function (data) {
            $('#quick_view').html(data);
            mfp_modal('#modal');
        }
    });
}

var onlineExamImagePickerState = {
    page: 1,
    perPage: 24,
    totalPages: 1,
    search: '',
    activeEditor: null,
    pageBranchId: 0
};

function initOnlineExamEditors() {
    if (!$(".question_note").length) {
        return;
    }
    ensureQuestionImagePickerModal();
    $(".question_note").each(function () {
        var $field = $(this);
        if ($field.next('.note-editor').length) {
            return;
        }
        $field.summernote({
            dialogsInBody: true,
            code: " ",
            height: 150,
            toolbar: [
                ["style", ["style"]],
                ["name", ["fontname", "fontsize"]],
                ["font", ["bold", "italic", "underline", "clear"]],
                ["color", ["color"]],
                ["para", ["ul", "ol", "paragraph"]],
                ["insert", ["link", "table", "picture", "gallery", "ocr", "emoji", "shapes"]],
                ["lang", ["langEn", "langHi", "osk"]],
                ["misc", ["fullscreen", "undo", "codeview"]]
            ],
            buttons: {
                gallery: function () {
                    return $.summernote.ui.button({
                        contents: '<i class="fas fa-images"></i>',
                        tooltip: 'Image Database',
                        click: function () {
                            onlineExamImagePickerState.activeEditor = $field;
                            openQuestionImagePicker();
                        }
                    }).render();
                },
                ocr: function () {
                    return $.summernote.ui.button({
                        contents: '<i class="fas fa-eye"></i> OCR',
                        tooltip: 'Extract text from image (OCR)',
                        click: function () {
                            onlineExamImagePickerState.activeEditor = $field;
                            openQuestionOcrModal();
                        }
                    }).render();
                },
                langEn: function () {
                    return $.summernote.ui.button({
                        className: 'note-btn-lang-en' + ((typeof window.getQuestionEditorLanguage === 'function' && window.getQuestionEditorLanguage() === 'en') ? ' active' : ''),
                        contents: 'EN',
                        tooltip: 'Type in English',
                        click: function () {
                            onlineExamImagePickerState.activeEditor = $field;
                            if (typeof window.setQuestionEditorLanguage === 'function') {
                                window.setQuestionEditorLanguage('en');
                            }
                        }
                    }).render();
                },
                langHi: function () {
                    return $.summernote.ui.button({
                        className: 'note-btn-lang-hi' + ((typeof window.getQuestionEditorLanguage === 'function' && window.getQuestionEditorLanguage() === 'hi') ? ' active' : ''),
                        contents: 'हिं',
                        tooltip: 'Type in Hindi (phonetic) — Ctrl+G',
                        click: function () {
                            onlineExamImagePickerState.activeEditor = $field;
                            if (typeof window.setQuestionEditorLanguage === 'function') {
                                window.setQuestionEditorLanguage('hi');
                            }
                        }
                    }).render();
                },
                osk: function () {
                    return $.summernote.ui.button({
                        className: 'note-btn-osk',
                        contents: '<i class="fas fa-keyboard"></i>',
                        tooltip: 'On-screen keyboard (EN / हिं)',
                        click: function () {
                            onlineExamImagePickerState.activeEditor = $field;
                            if (typeof window.toggleQuestionOsk === 'function') {
                                window.toggleQuestionOsk();
                            }
                        }
                    }).render();
                },
                emoji: function () {
                    return createInsertDropdownButton($field, '😊', 'Smileys', [
                        '😊', '😁', '😂', '😎', '😍', '🤔', '😢', '😡', '👍', '👎', '✅', '❌'
                    ]);
                },
                shapes: function () {
                    return createInsertDropdownButton($field, '◼', 'Shapes', [
                        '●', '○', '■', '□', '▲', '△', '◆', '◇', '★', '☆', '→', '✓'
                    ]);
                }
            },
            callbacks: {
                onFocus: function () {
                    onlineExamImagePickerState.activeEditor = $field;
                },
                onImageUpload: function (files) {
                    if (!files || !files.length) {
                        return;
                    }
                    var imageName = window.prompt('Enter image name for search:', '');
                    if (imageName === null) {
                        return;
                    }
                    imageName = $.trim(imageName);
                    if (!imageName) {
                        showImageDbFeedback('danger', 'Image name is required.');
                        return;
                    }
                    var fd = new FormData();
                    fd.append('image', files[0]);
                    fd.append('branch_id', getActiveEditorBranchId());
                    fd.append('image_name', imageName);
                    uploadQuestionImage(files[0], function (img) {
                        $field.summernote('insertImage', img.file_url, img.image_name || img.original_name || '');
                    }, fd);
                }
            }
        });
    });
    $('.question_note').on("summernote.enter", function (we, e) {
        $(this).summernote("pasteHTML", "<br><br>");
        e.preventDefault();
    });
    if (typeof window.refreshQuestionHindiInput === 'function') {
        window.refreshQuestionHindiInput();
    }
}

function createInsertDropdownButton($editor, iconText, tooltip, items) {
    var ui = $.summernote.ui;
    return ui.buttonGroup([
        ui.button({
            className: 'dropdown-toggle',
            contents: escapeHtml(iconText) + ' <span class="caret"></span>',
            tooltip: tooltip,
            data: { toggle: 'dropdown' }
        }),
        ui.dropdown({
            className: 'dropdown-style',
            items: items || [],
            click: function (e) {
                e.preventDefault();
                var value = $(e.target).data('value');
                if (value) {
                    $editor.summernote('pasteHTML', value + '&nbsp;');
                }
            }
        })
    ]).render();
}

function initImageDatabasePage() {
    var $page = $('#onlineexamImageDatabasePage');
    if (!$page.length) {
        return;
    }
    var branchId = parseInt($page.data('branch-id'), 10) || 0;
    onlineExamImagePickerState.pageBranchId = branchId;
    $('#imageDbUploadForm').on('submit', function (e) {
        e.preventDefault();
        var fd = new FormData(this);
        if (branchId > 0) {
            fd.set('branch_id', branchId);
        }
        uploadQuestionImage(fd.get('image'), function () {
            showImageDbFeedback('success', 'Image uploaded successfully.');
            resetImageDbPagination();
            fetchImageDatabaseList(true);
            $('#imageDbUploadForm')[0].reset();
        }, fd);
    });
    $('#imageDbUploadForm input[name="image"]').on('change', function () {
        var file = this.files && this.files.length ? this.files[0] : null;
        if (!file) {
            return;
        }
        var suggested = sanitizeImageName(file.name);
        var $nameInput = $('#imageDbUploadForm input[name="image_name"]');
        if ($nameInput.length && !$.trim($nameInput.val())) {
            $nameInput.val(suggested);
        }
    });
    $('#imageDbRefresh').on('click', function () {
        resetImageDbPagination();
        fetchImageDatabaseList(true);
    });
    $('#imageDbSearch').on('keyup', function () {
        onlineExamImagePickerState.search = $.trim($(this).val() || '');
        resetImageDbPagination();
        fetchImageDatabaseList(true);
    });
    $('#imageDbLoadMore').on('click', function () {
        if (onlineExamImagePickerState.page < onlineExamImagePickerState.totalPages) {
            onlineExamImagePickerState.page += 1;
            fetchImageDatabaseList(false);
        }
    });
    $('#imageDbSeedDefaults').on('click', function () {
        var fd = new FormData();
        fd.append('branch_id', onlineExamImagePickerState.pageBranchId || 0);
        appendCsrfToken(fd);
        $.ajax({
            url: base_url + 'onlineexam/image_seed_defaults',
            type: 'POST',
            data: fd,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function (res) {
                if (!res || res.status !== 'success') {
                    showImageDbFeedback('danger', (res && res.error) ? res.error : 'Failed to seed defaults.');
                    return;
                }
                showImageDbFeedback('success', res.message || 'Defaults added.');
                resetImageDbPagination();
                fetchImageDatabaseList(true);
            },
            error: function () {
                showImageDbFeedback('danger', 'Failed to seed defaults.');
            }
        });
    });
    renderDefaultMathShapeGallery();
    resetImageDbPagination();
    fetchImageDatabaseList(true);
}

function ensureQuestionImagePickerModal() {
    if ($('#questionImagePickerModal').length) {
        return;
    }
    var html = ''
        + '<div class="modal fade" id="questionImagePickerModal" tabindex="-1" role="dialog" aria-hidden="true">'
        + '  <div class="modal-dialog modal-lg" role="document">'
        + '    <div class="modal-content">'
        + '      <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Image Database</h4></div>'
        + '      <div class="modal-body">'
        + '        <div class="row mb-sm">'
        + '          <div class="col-md-7"><input type="text" class="form-control" id="questionImageSearch" placeholder="Search by image name"></div>'
        + '          <div class="col-md-5 text-right"><button type="button" class="btn btn-default" id="questionImageRefresh"><i class="fas fa-rotate"></i> Refresh</button></div>'
        + '        </div>'
        + '        <div id="questionImagePickerFeedback" class="mb-sm"></div>'
        + '        <div class="panel panel-default" style="margin-bottom:10px;">'
        + '          <div class="panel-heading"><strong>Default Mathematical Shapes</strong></div>'
        + '          <div class="panel-body"><div class="row" id="questionDefaultShapeGrid"></div></div>'
        + '        </div>'
        + '        <div class="row" id="questionImagePickerGrid"></div>'
        + '        <div class="text-center mt-md"><button type="button" class="btn btn-default" id="questionImagePickerLoadMore" style="display:none;">Load More</button></div>'
        + '      </div>'
        + '    </div>'
        + '  </div>'
        + '</div>';
    $('body').append(html);
    $(document).on('keyup', '#questionImageSearch', function () {
        onlineExamImagePickerState.search = $.trim($(this).val() || '');
        resetImageDbPagination();
        fetchImagePickerList(true);
    });
    $(document).on('click', '#questionImageRefresh', function () {
        resetImageDbPagination();
        fetchImagePickerList(true);
    });
    $(document).on('click', '#questionImagePickerLoadMore', function () {
        if (onlineExamImagePickerState.page < onlineExamImagePickerState.totalPages) {
            onlineExamImagePickerState.page += 1;
            fetchImagePickerList(false);
        }
    });
}

function openQuestionImagePicker() {
    ensureQuestionImagePickerModal();
    renderQuestionDefaultShapeGrid();
    resetImageDbPagination();
    $('#questionImageSearch').val(onlineExamImagePickerState.search || '');
    fetchImagePickerList(true);
    $('#questionImagePickerModal').modal('show');
}

function resetImageDbPagination() {
    onlineExamImagePickerState.page = 1;
    onlineExamImagePickerState.totalPages = 1;
}

function fetchImageDatabaseList(reset) {
    var branchId = onlineExamImagePickerState.pageBranchId || 0;
    $.ajax({
        url: base_url + 'onlineexam/image_list',
        type: 'GET',
        dataType: 'json',
        data: {
            page: onlineExamImagePickerState.page,
            per_page: onlineExamImagePickerState.perPage,
            search: onlineExamImagePickerState.search,
            branch_id: branchId
        },
        success: function (res) {
            if (!res || res.status !== 'success') {
                showImageDbFeedback('danger', 'Failed to load images.');
                return;
            }
            onlineExamImagePickerState.totalPages = (res.pagination && res.pagination.total_pages) ? parseInt(res.pagination.total_pages, 10) : 1;
            renderImageDatabaseGrid(res.images || [], reset);
            $('#imageDbLoadMore').toggle(onlineExamImagePickerState.page < onlineExamImagePickerState.totalPages);
        }
    });
}

function fetchImagePickerList(reset) {
    var branchId = getActiveEditorBranchId();
    $.ajax({
        url: base_url + 'onlineexam/image_list',
        type: 'GET',
        dataType: 'json',
        data: {
            page: onlineExamImagePickerState.page,
            per_page: onlineExamImagePickerState.perPage,
            search: onlineExamImagePickerState.search,
            branch_id: branchId
        },
        success: function (res) {
            if (!res || res.status !== 'success') {
                $('#questionImagePickerFeedback').html('<div class="alert alert-danger">Failed to load images.</div>');
                return;
            }
            onlineExamImagePickerState.totalPages = (res.pagination && res.pagination.total_pages) ? parseInt(res.pagination.total_pages, 10) : 1;
            renderQuestionImagePickerGrid(res.images || [], reset);
            $('#questionImagePickerLoadMore').toggle(onlineExamImagePickerState.page < onlineExamImagePickerState.totalPages);
        }
    });
}

function renderImageDatabaseGrid(images, reset) {
    var $grid = $('#imageDbGrid');
    if (reset) {
        $grid.html('');
    }
    if (!images.length && reset) {
        $grid.html('<div class="col-md-12"><p class="text-muted">No images found.</p></div>');
        return;
    }
    $.each(images, function (_, img) {
        var row = ''
            + '<div class="image-db-card-wrap" data-id="' + img.id + '">'
            + '  <div class="image-db-card">'
            + '    <img class="image-db-thumb" src="' + escapeHtml(img.file_url) + '" alt="">'
            + '    <div class="image-db-meta">'
            + '      <div class="image-db-name" title="' + escapeHtml(img.image_name || img.original_name || img.file_name || '') + '">' + escapeHtml(img.image_name || img.original_name || img.file_name || '') + '</div>'
            + '      <div class="btn-group btn-group-xs">'
            + '        <button type="button" class="btn btn-default btn-copy-image-url" data-url="' + escapeHtml(img.file_url) + '">Copy URL</button>'
            + '        <button type="button" class="btn btn-default btn-copy-image-html" data-url="' + escapeHtml(img.file_url) + '">Copy HTML</button>'
            + '        <button type="button" class="btn btn-default btn-insert-image" data-url="' + escapeHtml(img.file_url) + '">Insert</button>'
            + '        <button type="button" class="btn btn-danger btn-delete-image" data-id="' + img.id + '">Delete</button>'
            + '      </div>'
            + '    </div>'
            + '  </div>'
            + '</div>';
        $grid.append(row);
    });
}

function renderDefaultMathShapeGallery() {
    var $grid = $('#imageDbDefaultGrid');
    if (!$grid.length) {
        return;
    }
    $grid.html('');
    $.each(getDefaultMathShapeImages(), function (_, img) {
        var row = ''
            + '<div class="image-db-card-wrap">'
            + '  <div class="image-db-card">'
            + '    <img class="image-db-thumb" src="' + escapeHtml(img.file_url) + '" alt="">'
            + '    <div class="image-db-meta">'
            + '      <div class="image-db-name" title="' + escapeHtml(img.image_name) + '">' + escapeHtml(img.image_name) + '</div>'
            + '      <div class="btn-group btn-group-xs">'
            + '        <button type="button" class="btn btn-default btn-copy-image-url" data-url="' + escapeHtml(img.file_url) + '">Copy URL</button>'
            + '        <button type="button" class="btn btn-default btn-copy-image-html" data-url="' + escapeHtml(img.file_url) + '">Copy HTML</button>'
            + '        <button type="button" class="btn btn-default btn-insert-image" data-url="' + escapeHtml(img.file_url) + '">Insert</button>'
            + '      </div>'
            + '    </div>'
            + '  </div>'
            + '</div>';
        $grid.append(row);
    });
}

function getDefaultMathShapeImages() {
    function svgData(svg) {
        return 'data:image/svg+xml;utf8,' + encodeURIComponent(svg);
    }
    return [
        {
            image_name: 'Circle',
            file_url: svgData('<svg xmlns="http://www.w3.org/2000/svg" width="220" height="160"><rect width="100%" height="100%" fill="white"/><circle cx="110" cy="80" r="52" fill="none" stroke="black" stroke-width="4"/></svg>')
        },
        {
            image_name: 'Triangle',
            file_url: svgData('<svg xmlns="http://www.w3.org/2000/svg" width="220" height="160"><rect width="100%" height="100%" fill="white"/><polygon points="110,24 36,132 184,132" fill="none" stroke="black" stroke-width="4"/></svg>')
        },
        {
            image_name: 'Square',
            file_url: svgData('<svg xmlns="http://www.w3.org/2000/svg" width="220" height="160"><rect width="100%" height="100%" fill="white"/><rect x="60" y="30" width="100" height="100" fill="none" stroke="black" stroke-width="4"/></svg>')
        },
        {
            image_name: 'Rectangle',
            file_url: svgData('<svg xmlns="http://www.w3.org/2000/svg" width="220" height="160"><rect width="100%" height="100%" fill="white"/><rect x="40" y="45" width="140" height="70" fill="none" stroke="black" stroke-width="4"/></svg>')
        },
        {
            image_name: 'Pentagon',
            file_url: svgData('<svg xmlns="http://www.w3.org/2000/svg" width="220" height="160"><rect width="100%" height="100%" fill="white"/><polygon points="110,20 42,70 68,138 152,138 178,70" fill="none" stroke="black" stroke-width="4"/></svg>')
        },
        {
            image_name: 'Hexagon',
            file_url: svgData('<svg xmlns="http://www.w3.org/2000/svg" width="220" height="160"><rect width="100%" height="100%" fill="white"/><polygon points="65,30 155,30 200,80 155,130 65,130 20,80" fill="none" stroke="black" stroke-width="4"/></svg>')
        },
        {
            image_name: 'Number Line 0 to 20',
            file_url: svgData('<svg xmlns="http://www.w3.org/2000/svg" width="220" height="160"><rect width="100%" height="100%" fill="white"/><line x1="20" y1="85" x2="200" y2="85" stroke="black" stroke-width="3"/><text x="22" y="75" font-size="12">0 2 4 6 8 10 12 14 16 18 20</text></svg>')
        },
        {
            image_name: 'Fraction Circle',
            file_url: svgData('<svg xmlns="http://www.w3.org/2000/svg" width="220" height="160"><rect width="100%" height="100%" fill="white"/><circle cx="110" cy="80" r="52" fill="none" stroke="black" stroke-width="3"/><line x1="110" y1="28" x2="110" y2="132" stroke="black" stroke-width="3"/><text x="94" y="154" font-size="12">1/2</text></svg>')
        },
        {
            image_name: 'Angle Types',
            file_url: svgData('<svg xmlns="http://www.w3.org/2000/svg" width="220" height="160"><rect width="100%" height="100%" fill="white"/><text x="12" y="28" font-size="12">Acute</text><text x="84" y="28" font-size="12">Right</text><text x="152" y="28" font-size="12">Obtuse</text><path d="M20 120 L50 80 L70 120" fill="none" stroke="black" stroke-width="3"/><path d="M95 120 L95 80 L135 120" fill="none" stroke="black" stroke-width="3"/><path d="M150 120 L170 90 L205 120" fill="none" stroke="black" stroke-width="3"/></svg>')
        },
        {
            image_name: 'Coordinate Grid',
            file_url: svgData('<svg xmlns="http://www.w3.org/2000/svg" width="220" height="160"><rect width="100%" height="100%" fill="white"/><line x1="20" y1="80" x2="200" y2="80" stroke="black" stroke-width="2"/><line x1="110" y1="20" x2="110" y2="140" stroke="black" stroke-width="2"/><text x="198" y="74" font-size="10">x</text><text x="114" y="24" font-size="10">y</text></svg>')
        },
        {
            image_name: 'Cube',
            file_url: svgData('<svg xmlns="http://www.w3.org/2000/svg" width="220" height="160"><rect width="100%" height="100%" fill="white"/><rect x="55" y="55" width="70" height="70" fill="none" stroke="black" stroke-width="3"/><rect x="90" y="35" width="70" height="70" fill="none" stroke="black" stroke-width="3"/><line x1="55" y1="55" x2="90" y2="35" stroke="black" stroke-width="3"/><line x1="125" y1="55" x2="160" y2="35" stroke="black" stroke-width="3"/><line x1="125" y1="125" x2="160" y2="105" stroke="black" stroke-width="3"/><line x1="55" y1="125" x2="90" y2="105" stroke="black" stroke-width="3"/></svg>')
        },
        {
            image_name: 'Cone',
            file_url: svgData('<svg xmlns="http://www.w3.org/2000/svg" width="220" height="160"><rect width="100%" height="100%" fill="white"/><ellipse cx="110" cy="120" rx="52" ry="12" fill="none" stroke="black" stroke-width="3"/><line x1="58" y1="120" x2="110" y2="30" stroke="black" stroke-width="3"/><line x1="162" y1="120" x2="110" y2="30" stroke="black" stroke-width="3"/></svg>')
        },
        {
            image_name: 'Cylinder',
            file_url: svgData('<svg xmlns="http://www.w3.org/2000/svg" width="220" height="160"><rect width="100%" height="100%" fill="white"/><ellipse cx="110" cy="35" rx="45" ry="12" fill="none" stroke="black" stroke-width="3"/><ellipse cx="110" cy="125" rx="45" ry="12" fill="none" stroke="black" stroke-width="3"/><line x1="65" y1="35" x2="65" y2="125" stroke="black" stroke-width="3"/><line x1="155" y1="35" x2="155" y2="125" stroke="black" stroke-width="3"/></svg>')
        },
        {
            image_name: 'Symmetry Line',
            file_url: svgData('<svg xmlns="http://www.w3.org/2000/svg" width="220" height="160"><rect width="100%" height="100%" fill="white"/><rect x="50" y="35" width="120" height="90" fill="none" stroke="black" stroke-width="3"/><line x1="110" y1="25" x2="110" y2="135" stroke="red" stroke-width="2" stroke-dasharray="5,4"/></svg>')
        },
        {
            image_name: 'Graph Paper Dense',
            file_url: svgData('<svg xmlns="http://www.w3.org/2000/svg" width="220" height="160"><rect width="100%" height="100%" fill="white"/><g stroke="#999" stroke-width="0.8">' +
                '<line x1="20" y1="20" x2="20" y2="140"/><line x1="40" y1="20" x2="40" y2="140"/><line x1="60" y1="20" x2="60" y2="140"/><line x1="80" y1="20" x2="80" y2="140"/><line x1="100" y1="20" x2="100" y2="140"/><line x1="120" y1="20" x2="120" y2="140"/><line x1="140" y1="20" x2="140" y2="140"/><line x1="160" y1="20" x2="160" y2="140"/><line x1="180" y1="20" x2="180" y2="140"/><line x1="200" y1="20" x2="200" y2="140"/>' +
                '<line x1="20" y1="20" x2="200" y2="20"/><line x1="20" y1="40" x2="200" y2="40"/><line x1="20" y1="60" x2="200" y2="60"/><line x1="20" y1="80" x2="200" y2="80"/><line x1="20" y1="100" x2="200" y2="100"/><line x1="20" y1="120" x2="200" y2="120"/><line x1="20" y1="140" x2="200" y2="140"/>' +
                '</g></svg>')
        }
    ];
}

function renderQuestionDefaultShapeGrid() {
    var $grid = $('#questionDefaultShapeGrid');
    if (!$grid.length) {
        return;
    }
    $grid.html('');
    $.each(getDefaultMathShapeImages(), function (_, img) {
        var card = ''
            + '<div class="col-md-3 col-sm-4 col-xs-6 mb-sm">'
            + '  <div class="thumbnail" style="margin-bottom:8px;">'
            + '    <img src="' + escapeHtml(img.file_url) + '" style="height:120px; width:100%; object-fit:cover;" alt="">'
            + '    <div class="caption" style="padding:6px 4px 2px;">'
            + '      <div style="font-size:11px; margin-bottom:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">' + escapeHtml(img.image_name) + '</div>'
            + '      <button type="button" class="btn btn-default btn-xs btn-block btn-insert-image" data-url="' + escapeHtml(img.file_url) + '">Insert</button>'
            + '    </div>'
            + '  </div>'
            + '</div>';
        $grid.append(card);
    });
}

function renderQuestionImagePickerGrid(images, reset) {
    var $grid = $('#questionImagePickerGrid');
    if (reset) {
        $grid.html('');
    }
    if (!images.length && reset) {
        $grid.html('<div class="col-md-12"><p class="text-muted">No images found.</p></div>');
        return;
    }
    $.each(images, function (_, img) {
        var card = ''
            + '<div class="col-md-3 col-sm-4 col-xs-6 mb-sm">'
            + '  <div class="thumbnail" style="margin-bottom:8px;">'
            + '    <img src="' + escapeHtml(img.file_url) + '" style="height:120px; width:100%; object-fit:cover;" alt="">'
            + '    <div class="caption" style="padding:6px 4px 2px;">'
            + '      <button type="button" class="btn btn-default btn-xs btn-block btn-insert-image" data-url="' + escapeHtml(img.file_url) + '">Insert</button>'
            + '    </div>'
            + '  </div>'
            + '</div>';
        $grid.append(card);
    });
}

function uploadQuestionImage(file, successCallback, preparedFormData) {
    var fd = preparedFormData instanceof FormData ? preparedFormData : new FormData();
    if (!(preparedFormData instanceof FormData)) {
        fd.append('image', file);
        fd.append('branch_id', getActiveEditorBranchId());
    }
    appendCsrfToken(fd);
    $.ajax({
        url: base_url + 'onlineexam/image_upload',
        type: 'POST',
        data: fd,
        dataType: 'json',
        processData: false,
        contentType: false,
        success: function (res) {
            if (!res || res.status !== 'success') {
                var msg = (res && res.error) ? res.error : 'Image upload failed.';
                showImageDbFeedback('danger', msg);
                return;
            }
            if (typeof successCallback === 'function') {
                successCallback(res.image);
            }
        },
        error: function (xhr) {
            var msg = 'Image upload failed.';
            if (xhr && xhr.responseText && xhr.responseText.indexOf('csrf') !== -1) {
                msg = 'Upload blocked by security token. Refresh page and try again.';
            }
            showImageDbFeedback('danger', msg);
        }
    });
}

function showImageDbFeedback(type, msg) {
    var html = '<div class="alert alert-' + type + '">' + escapeHtml(msg) + '</div>';
    if ($('#imageDbFeedback').length) {
        $('#imageDbFeedback').html(html);
    }
    if ($('#questionImagePickerFeedback').length) {
        $('#questionImagePickerFeedback').html(html);
    }
}

function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text);
        return;
    }
    var $tmp = $('<input>');
    $('body').append($tmp);
    $tmp.val(text).select();
    document.execCommand('copy');
    $tmp.remove();
}

function escapeHtml(str) {
    return String(str || '').replace(/[&<>"']/g, function (m) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
    });
}

function sanitizeImageName(fileName) {
    var base = String(fileName || '').replace(/\.[^/.]+$/, '');
    base = base.replace(/[_-]+/g, ' ');
    base = base.replace(/\s+/g, ' ').trim();
    if (!base) {
        base = 'image';
    }
    if (base.length > 120) {
        base = base.substring(0, 120).trim();
    }
    return base;
}

function getActiveEditorBranchId() {
    var $editor = onlineExamImagePickerState.activeEditor;
    if ($editor && $editor.length) {
        var $form = $editor.closest('form');
        if ($form.length) {
            var value = parseInt($form.find('input[name="branch_id"]').first().val(), 10);
            if (!isNaN(value) && value > 0) {
                return value;
            }
        }
    }
    return 0;
}

function appendCsrfToken(formData) {
    if (!(formData instanceof FormData)) {
        return;
    }
    var $csrfInput = $('input[name*="csrf"]').first();
    if (!$csrfInput.length) {
        return;
    }
    var csrfName = $csrfInput.attr('name');
    var csrfValue = $csrfInput.val();
    if (csrfName && csrfValue) {
        formData.append(csrfName, csrfValue);
    }
}

$(document).on('click', '.btn-copy-image-url', function () {
    copyToClipboard($(this).data('url') || '');
    showImageDbFeedback('success', 'Image URL copied.');
});

$(document).on('click', '.btn-copy-image-html', function () {
    var url = $(this).data('url') || '';
    if (!url) {
        return;
    }
    copyToClipboard('<img src="' + url + '" alt="">');
    showImageDbFeedback('success', 'Image HTML snippet copied.');
});

$(document).on('click', '.btn-insert-image', function () {
    var url = $(this).data('url') || '';
    if (!url) {
        return;
    }
    var $editor = onlineExamImagePickerState.activeEditor;
    if ($editor && $editor.length && typeof $editor.summernote === 'function') {
        $editor.summernote('insertImage', url, 'question-image');
    } else {
        copyToClipboard('<img src="' + url + '" alt="">');
        showImageDbFeedback('success', 'No active editor found. HTML snippet copied.');
    }
    $('#questionImagePickerModal').modal('hide');
});

$(document).on('click', '.btn-delete-image', function () {
    var imageId = $(this).data('id');
    if (!imageId) {
        return;
    }
    var fd = new FormData();
    appendCsrfToken(fd);
    if (onlineExamImagePickerState.pageBranchId > 0) {
        fd.append('branch_id', onlineExamImagePickerState.pageBranchId);
    }
    $.ajax({
        url: base_url + 'onlineexam/image_delete/' + imageId,
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (res) {
            if (!res || res.status !== 'success') {
                showImageDbFeedback('danger', (res && res.error) ? res.error : 'Delete failed.');
                return;
            }
            showImageDbFeedback('success', 'Image deleted.');
            resetImageDbPagination();
            fetchImageDatabaseList(true);
            fetchImagePickerList(true);
        },
        error: function () {
            showImageDbFeedback('danger', 'Delete failed.');
        }
    });
});

// Student portal: online exam take page
var examDuration = '';
var totalQuestions = 0;
var currentStep = 1;
var interval;

function examPortalAlert(msg, type) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: type === 'error' ? 'error' : 'success',
            title: type === 'error' ? 'Error' : 'Success',
            text: msg
        });
    } else if (typeof alertMsg === 'function') {
        alertMsg(msg, type);
    } else {
        alert(msg);
    }
}

function mountExamModal() {
    var $modal = $('#ans_modalBox');
    if ($modal.length && !$modal.parent().is('body')) {
        $modal.appendTo('body');
    }
}

function openExamModal() {
    mountExamModal();
    var $modal = $('#ans_modalBox');
    if (!$modal.length) {
        return;
    }
    if (typeof $modal.modal === 'function') {
        $modal.modal({ show: true, backdrop: 'static', keyboard: false });
        return;
    }
    $('body').addClass('modal-open');
    if (!$('.modal-backdrop').length) {
        $('<div class="modal-backdrop fade in"></div>').appendTo('body');
    }
    $modal.css('display', 'block').addClass('in show').attr('aria-hidden', 'false');
}

function initExamWizard() {
    var $wizard = $('#fueluxWizard');
    if (!$wizard.length || !$.fn.wizard) {
        return;
    }
    try {
        if ($wizard.data('fu.wizard')) {
            $wizard.off('actionclicked.fu.wizard');
        } else {
            $wizard.wizard();
        }
        $wizard.off('actionclicked.fu.wizard').on('actionclicked.fu.wizard', function (e, data) {
            var steps = data.direction === 'next' ? data.step + 1 : data.step - 1;
            var btn = $('#question' + steps).addClass('active');
            $('.exam-que-btn').not(btn).removeClass('active');
            if (steps === totalQuestions) {
                $('#nextbutton i').remove();
                $('#nextbutton').append(' <i class="fas fa-check"></i>');
                $('#finishedbutton').hide();
            } else if (steps === totalQuestions + 1) {
                $('#answerForm').submit();
            } else {
                $('#nextbutton i').remove();
                $('#nextbutton').append(' <i class="fa fa-angle-right"></i>');
                $('#finishedbutton').show();
            }
            currentStep = steps;
            makeAnswered(data.step);
        });
    } catch (err) {
        console.error('Exam wizard init failed:', err);
    }
}

function startExamTimer() {
    if (interval) {
        clearInterval(interval);
    }
    interval = setInterval(function () {
        $('.remain_duration').text(durationUpdate());
    }, 1000);
}

function changeQuestion(questionID) {
    makeAnswered(currentStep);
    currentStep = questionID;
    makeAnswered(questionID);
    if ($('#fueluxWizard').data('fu.wizard')) {
        $('#fueluxWizard').wizard('selectedItem', { step: questionID });
    }
    var btn = $('#question' + questionID).addClass('active');
    $('.exam-que-btn').not(btn).removeClass('active');
    if (questionID === totalQuestions) {
        $('#nextbutton i').remove();
        $('#nextbutton').append(' <i class="fas fa-check"></i>');
        $('#finishedbutton').hide();
    } else {
        $('#nextbutton i').remove();
        $('#nextbutton').append(' <i class="fa fa-angle-right"></i>');
        $('#finishedbutton').show();
    }
}

function completeExams() {
    $('#answerForm').submit();
}

$(function () {
    mountExamModal();

    $(document).on('click', '.start_btn', function () {
        var $this = $(this);
        var examID = $this.attr('data-examid');
        examDuration = $this.attr('data-duration') || examDuration;
        $.ajax({
            type: 'POST',
            url: base_url + 'userrole/ajaxQuestions',
            data: { exam_id: examID },
            dataType: 'json',
            beforeSend: function () {
                $this.prop('disabled', true);
                if (interval) {
                    clearInterval(interval);
                }
            },
            success: function (data) {
                if (!data || data.status != 1) {
                    examPortalAlert((data && data.message) ? data.message : 'Unable to start the exam.', 'error');
                    return;
                }
                if (!$('#online_questions').length) {
                    examPortalAlert('Exam container not found.', 'error');
                    return;
                }
                totalQuestions = parseInt(data.total_questions, 10) || 0;
                $('#online_questions').html(data.page);
                openExamModal();
                initExamWizard();
                startExamTimer();
            },
            error: function () {
                examPortalAlert('Error occurred. Please try again.', 'error');
            },
            complete: function () {
                $this.prop('disabled', false);
            }
        });
    });
});

/* --------------------------------------------------------------------------
 * Question editor OCR (browser Tesseract.js) — extract text from image
 * -------------------------------------------------------------------------- */
var questionOcrState = {
    activeEditor: null,
    imageDataUrl: null,
    tesseractLoading: false,
    worker: null
};

function openQuestionOcrModal() {
    ensureQuestionOcrModal();
    questionOcrState.activeEditor = onlineExamImagePickerState.activeEditor || null;
    questionOcrState.imageDataUrl = null;
    $('#questionOcrFile').val('');
    $('#questionOcrPreview').attr('src', '').hide();
    $('#questionOcrDropHint').show();
    $('#questionOcrResult').val('');
    $('#questionOcrProgress').hide();
    $('#questionOcrProgressBar').css('width', '0%');
    $('#questionOcrStatus').text('');
    $('#questionOcrModal').modal('show');
}

function ensureQuestionOcrModal() {
    if ($('#questionOcrModal').length) {
        return;
    }
    var html = ''
        + '<div class="modal fade" id="questionOcrModal" tabindex="-1" role="dialog" aria-hidden="true">'
        + '  <div class="modal-dialog modal-lg" role="document">'
        + '    <div class="modal-content">'
        + '      <div class="modal-header">'
        + '        <button type="button" class="close" data-dismiss="modal">&times;</button>'
        + '        <h4 class="modal-title"><i class="fas fa-eye"></i> OCR — Extract text from image</h4>'
        + '      </div>'
        + '      <div class="modal-body">'
        + '        <p class="text-muted" style="margin-top:0;">Upload or paste a photo of a question paper. Text will be extracted and can be inserted into the editor.</p>'
        + '        <div class="row">'
        + '          <div class="col-md-6">'
        + '            <div class="form-group">'
        + '              <label>Language</label>'
        + '              <select id="questionOcrLang" class="form-control">'
        + '                <option value="eng">English</option>'
        + '                <option value="hin">Hindi</option>'
        + '                <option value="eng+hin" selected>English + Hindi</option>'
        + '              </select>'
        + '            </div>'
        + '            <div class="form-group">'
        + '              <label>Image</label>'
        + '              <input type="file" id="questionOcrFile" class="form-control" accept="image/*">'
        + '              <p class="help-block">Or paste image here with <kbd>Ctrl+V</kbd> / <kbd>⌘V</kbd>.</p>'
        + '            </div>'
        + '            <div id="questionOcrDrop" style="border:1px dashed #cbd5e1;border-radius:8px;min-height:180px;background:#f8fafc;display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative;">'
        + '              <span id="questionOcrDropHint" class="text-muted">Image preview</span>'
        + '              <img id="questionOcrPreview" alt="OCR preview" style="max-width:100%;max-height:220px;display:none;">'
        + '            </div>'
        + '            <div class="mt-md">'
        + '              <button type="button" class="btn btn-primary" id="questionOcrRun"><i class="fas fa-wand-magic-sparkles"></i> Extract Text</button>'
        + '              <button type="button" class="btn btn-default" id="questionOcrClear">Clear</button>'
        + '            </div>'
        + '            <div id="questionOcrProgress" class="progress progress-striped active mt-md" style="display:none;height:18px;margin-bottom:6px;">'
        + '              <div class="progress-bar progress-bar-info" id="questionOcrProgressBar" style="width:0%"></div>'
        + '            </div>'
        + '            <div id="questionOcrStatus" class="text-muted small"></div>'
        + '          </div>'
        + '          <div class="col-md-6">'
        + '            <div class="form-group">'
        + '              <label>Extracted text <small class="text-muted">(editable)</small></label>'
        + '              <textarea id="questionOcrResult" class="form-control" rows="16" placeholder="OCR result will appear here…" style="font-size:13px;"></textarea>'
        + '            </div>'
        + '          </div>'
        + '        </div>'
        + '      </div>'
        + '      <div class="modal-footer">'
        + '        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>'
        + '        <button type="button" class="btn btn-success" id="questionOcrInsert"><i class="fas fa-file-import"></i> Insert into editor</button>'
        + '        <button type="button" class="btn btn-primary" id="questionOcrReplace"><i class="fas fa-exchange-alt"></i> Replace editor</button>'
        + '      </div>'
        + '    </div>'
        + '  </div>'
        + '</div>';
    $('body').append(html);

    $('#questionOcrFile').on('change', function () {
        var file = this.files && this.files[0] ? this.files[0] : null;
        if (file) {
            readQuestionOcrFile(file);
        }
    });

    $('#questionOcrDrop').on('dragover', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).css('border-color', '#0088cc');
    }).on('dragleave drop', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).css('border-color', '#cbd5e1');
        if (e.type === 'drop' && e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files.length) {
            readQuestionOcrFile(e.originalEvent.dataTransfer.files[0]);
        }
    });

    $(document).on('paste.questionOcr', function (e) {
        if (!$('#questionOcrModal').is(':visible')) {
            return;
        }
        var items = (e.originalEvent.clipboardData || {}).items || [];
        for (var i = 0; i < items.length; i++) {
            if (items[i].type && items[i].type.indexOf('image') === 0) {
                e.preventDefault();
                readQuestionOcrFile(items[i].getAsFile());
                break;
            }
        }
    });

    $('#questionOcrClear').on('click', function () {
        questionOcrState.imageDataUrl = null;
        $('#questionOcrFile').val('');
        $('#questionOcrPreview').attr('src', '').hide();
        $('#questionOcrDropHint').show();
        $('#questionOcrResult').val('');
        $('#questionOcrProgress').hide();
        $('#questionOcrStatus').text('');
    });

    $('#questionOcrRun').on('click', function () {
        runQuestionOcr();
    });

    $('#questionOcrInsert').on('click', function () {
        insertQuestionOcrText(false);
    });

    $('#questionOcrReplace').on('click', function () {
        insertQuestionOcrText(true);
    });
}

function readQuestionOcrFile(file) {
    if (!file || !file.type || file.type.indexOf('image') !== 0) {
        $('#questionOcrStatus').text('Please select a valid image file.');
        return;
    }
    var reader = new FileReader();
    reader.onload = function (ev) {
        questionOcrState.imageDataUrl = ev.target.result;
        $('#questionOcrPreview').attr('src', questionOcrState.imageDataUrl).show();
        $('#questionOcrDropHint').hide();
        $('#questionOcrStatus').text((file.name || 'image') + ' ready — click Extract Text.');
    };
    reader.readAsDataURL(file);
}

function loadTesseractJs(cb) {
    if (window.Tesseract && typeof window.Tesseract.recognize === 'function') {
        cb();
        return;
    }
    if (questionOcrState.tesseractLoading) {
        var tries = 0;
        var t = setInterval(function () {
            tries++;
            if (window.Tesseract && typeof window.Tesseract.recognize === 'function') {
                clearInterval(t);
                cb();
            } else if (tries > 60) {
                clearInterval(t);
                $('#questionOcrStatus').text('Failed to load OCR library. Check internet connection.');
            }
        }, 250);
        return;
    }
    questionOcrState.tesseractLoading = true;
    $('#questionOcrStatus').text('Loading OCR engine…');
    $.getScript('https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js')
        .done(function () {
            questionOcrState.tesseractLoading = false;
            cb();
        })
        .fail(function () {
            questionOcrState.tesseractLoading = false;
            $('#questionOcrStatus').text('Failed to load OCR library. Check internet connection.');
        });
}

function runQuestionOcr() {
    if (!questionOcrState.imageDataUrl) {
        $('#questionOcrStatus').text('Please upload or paste an image first.');
        return;
    }
    var lang = $('#questionOcrLang').val() || 'eng+hin';
    var $btn = $('#questionOcrRun');
    loadTesseractJs(function () {
        $btn.prop('disabled', true);
        $('#questionOcrProgress').show();
        $('#questionOcrProgressBar').css('width', '0%');
        $('#questionOcrStatus').text('Recognizing text…');
        window.Tesseract.recognize(questionOcrState.imageDataUrl, lang, {
            logger: function (m) {
                if (!m) {
                    return;
                }
                if (m.status === 'recognizing text' && typeof m.progress === 'number') {
                    var pct = Math.round(m.progress * 100);
                    $('#questionOcrProgressBar').css('width', pct + '%');
                    $('#questionOcrStatus').text('Recognizing… ' + pct + '%');
                } else if (m.status) {
                    $('#questionOcrStatus').text(m.status + '…');
                }
            }
        }).then(function (result) {
            var text = '';
            if (result && result.data && typeof result.data.text === 'string') {
                text = result.data.text;
            } else if (result && typeof result.text === 'string') {
                text = result.text;
            }
            text = $.trim(text || '');
            $('#questionOcrResult').val(text);
            $('#questionOcrProgressBar').css('width', '100%');
            $('#questionOcrStatus').text(text ? 'Done. Review text, then Insert.' : 'No text detected. Try a clearer image.');
        }, function (err) {
            $('#questionOcrStatus').text('OCR failed: ' + (err && err.message ? err.message : 'unknown error'));
        }).then(function () {
            $btn.prop('disabled', false);
            setTimeout(function () {
                $('#questionOcrProgress').fadeOut(200);
            }, 600);
        });
    });
}

function insertQuestionOcrText(replace) {
    var text = $.trim($('#questionOcrResult').val() || '');
    if (!text) {
        $('#questionOcrStatus').text('Nothing to insert — extract text first.');
        return;
    }
    var $editor = questionOcrState.activeEditor || onlineExamImagePickerState.activeEditor;
    if (!$editor || !$editor.length || typeof $editor.summernote !== 'function') {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text);
            $('#questionOcrStatus').text('No active editor — text copied to clipboard.');
        } else {
            $('#questionOcrStatus').text('No active editor found.');
        }
        return;
    }
    var safe = escapeHtml(text).replace(/\n/g, '<br>');
    if (replace) {
        $editor.summernote('code', '<p>' + safe + '</p>');
    } else {
        $editor.summernote('pasteHTML', '<p>' + safe + '</p>');
    }
    onlineExamImagePickerState.activeEditor = $editor;
    $('#questionOcrModal').modal('hide');
    if (typeof window.refreshQuestionHindiInput === 'function') {
        setTimeout(window.refreshQuestionHindiInput, 100);
    }
}
