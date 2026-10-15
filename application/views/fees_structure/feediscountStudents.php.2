<div class="card">
    <div class="card-header">
        <h3>Fee Discount Applied Students</h3>
    </div>

    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <label><strong>Filter by Fee Head</strong></label>
                <select id="feeHeadFilter" class="form-control">
                    <option value="">All Fee Heads</option>
                    <?php
                    $feeHeads = array_unique(array_column($discount_students, 'fees_type'));
                    foreach ($feeHeads as $fee) :
                    ?>
                        <option value="<?= $fee ?>"><?= $fee ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <table class="table table-bordered table-striped" id="discountTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Register No</th>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Fee Head</th>
                    <th>Discount Name</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Applied On</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php
                if (!empty($discount_students)) :
                    $i = 1;
                    $totalAmount = 0; // 🔹 total initialization

                    foreach ($discount_students as $row) :

                        // 🔹 add only FIXED discount to total
                        if ($row['discount_type'] === 'fixed') {
                            $totalAmount += (float) $row['fixed_amount'];
                        }
                ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= $row['register_no'] ?></td>
                            <td><?= $row['student_name'] ?></td>
                            <td><?= $row['class_name'] ?? '-' ?></td>
                            <td><?= $row['section_name'] ?? '-' ?></td>
                            <td><?= $row['fees_type'] ?></td>
                            <td><?= $row['discount_name'] ?></td>
                            <td><?= ucfirst($row['discount_type']) ?></td>
                            <td>
                                <?= ($row['discount_type'] === 'percentage')
                                    ? $row['percentage'] . '%'
                                    : number_format($row['fixed_amount'], 2) ?>
                            </td>
                            <td><?= date('d-m-Y', strtotime($row['applied_on'])) ?></td>
                            <td>
                                <button
                                    class="btn btn-danger btn-sm remove-discount"
                                    data-id="<?= $row['sfd_id'] ?>"
                                    title="Remove Discount">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                <?php
                    endforeach;
                endif;
                ?>
            </tbody>
            
        </table>

    </div>
</div>
<script>
    let discountTable;

    $(document).ready(function() {

        discountTable = $('#discountTable').DataTable({
            order: []
        });

        // Fee Head filter (column index = 5)
        $('#feeHeadFilter').on('change', function() {
            let value = $(this).val();
            discountTable
                .column(5) // Fee Head column index
                .search(value)
                .draw();
        });

    });
</script>



<script>
    $(document).on('click', '.remove-discount', function() {

        let btn = $(this);
        let id = btn.data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: 'This discount will be removed from the student.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, remove it',
            cancelButtonText: 'Cancel'
        }).then((result) => {

            if (!result.isConfirmed) return;

            $.ajax({
                url: "<?= base_url('fees/removeStudentFeeDiscount') ?>",
                type: "POST",
                data: {
                    id: id
                },
                dataType: "json",
                success: function(response) {

                    if (response.status === 'success') {

                        // ✅ refresh DataTable row
                        discountTable
                            .row(btn.closest('tr'))
                            .remove()
                            .draw(false);

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: response.message,
                            showConfirmButton: false,
                            timer: 3000
                        });

                    } else {

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: response.message,
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                }
            });

        });
    });
</script>