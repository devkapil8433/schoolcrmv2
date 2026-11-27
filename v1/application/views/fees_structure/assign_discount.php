   <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<div class="content-wrapper">
    <section class="content-header">
        <h1>Assign Fees Discount</h1>
    </section>

    <section class="content">

        <!-- 🔹 Discount Info -->
        <div class="box box-primary">
            <div class="box-body">
                <strong>Discount:</strong> <?= $discount['name']; ?>
                &nbsp; | &nbsp;
                <strong>Code:</strong> <?= $discount['discount_code']; ?>
                &nbsp; | &nbsp;
                <strong>Value:</strong>
                <?php if ($discount['discount_type'] == 'percentage'): ?>
                    <?= $discount['percentage']; ?> %
                <?php else: ?>
                    ₹ <?= $discount['fixed_amount']; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- 🔹 Search -->
        <div class="box box-info">
            <div class="box-body">
                <div class="row" style="margin-bottom: 25px;">

                    <div class="col-md-4">
                        <label>Class</label>
                        <select id="class_id" class="form-control">
                            <option value="">Select</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= $class['id']; ?>">
                                    <?= $class['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Section</label>
                        <select id="section_id" class="form-control">
                            <option value="">Select</option>
                            <?php foreach ($sections as $section): ?>
                                <option value="<?= $section['id']; ?>">
                                    <?= $section['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4" style="margin-top:25px;">
                        <button id="searchStudents" class="btn btn-primary">
                            Search Students
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- 🔹 Student List -->
        <div class="box box-success">
            <div class="box-body">

                <form id="assignDiscountForm">
                    <input type="hidden" name="discount_id" value="<?= $discount['id']; ?>">

                    <table class="table table-bordered" id="studentTable">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="select_all">
                                </th>
                                <th>Register No</th>
                                <th>Student Name</th>
                                <th>Class</th>
                                <th>Section</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center text-danger">
                                    Select class & section to search students
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <button type="submit" id="assignDiscountBtn" class="btn btn-success">
                        Assign Discount
                    </button>
                </form>

            </div>
        </div>

    </section>
</div>
<script>
$(document).ready(function () {

    const baseUrl = "<?= base_url(); ?>";

    // 🔹 Search Students
    $('#searchStudents').click(function () {

        let class_id   = $('#class_id').val();
        let section_id = $('#section_id').val();

        if (!class_id || !section_id) {
            alert('Please select class and section');
            return;
        }

        $.ajax({
            url: baseUrl + "fees/search_students_for_discount",
            type: "POST",
            data: {
                class_id: class_id,
                section_id: section_id,
                '<?= $this->security->get_csrf_token_name(); ?>':
                '<?= $this->security->get_csrf_hash(); ?>'
            },
            dataType: "json",
            success: function (res) {

                let html = '';

                if (res.length === 0) {
                    html = `<tr>
                        <td colspan="5" class="text-center text-danger">
                            No students found
                        </td>
                    </tr>`;
                } else {
                    $.each(res, function (i, s) {
                        html += `
                            <tr>
                                <td>
                                    <input type="checkbox" class="studentCheckbox" name="student_ids[]" value="${s.student_id}">
                                </td>
                                <td>${s.register_no}</td>
                                <td>${s.student_name}</td>
                                <td>${s.class_name}</td>
                                <td>${s.section_name}</td>
                            </tr>
                        `;
                    });
                }

                $('#studentTable tbody').html(html);
            }
        });
    });

    // 🔹 Select All
    $('#select_all').click(function () {
        $('input[name="student_ids[]"]').prop('checked', this.checked);
    });

});
</script>
<script>
$('#assignDiscountBtn').on('click', function () {

    let students = [];
    $('.studentCheckbox:checked').each(function () {
        students.push($(this).val());
    });

    if (students.length === 0) {
        alert('Please select at least one student');
        return;
    }

    $.ajax({
        url: "<?= base_url('fees/apply_discount') ?>",
        type: "POST",
        data: {
            discount_id: <?= $discount['id'] ?>,
            student_ids: students
        },
        dataType: "json",
        success: function (res) {
            alert(res.message);
        }
    });
});
</script>
