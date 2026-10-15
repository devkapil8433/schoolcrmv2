<div class="container">
    <h2>Family Fee Receipts</h2>

    <!-- Search Bar -->
    <div class="row mb-3">
        <div class="col-md-6">
            <input type="text" id="parent_name" class="form-control" placeholder="Enter Guardian Name">
        </div>
        <div class="col-md-2">
            <button id="searchBtn" class="btn btn-primary">Search</button>
        </div>
        <div class="mb-2">
            <strong>Date:</strong> <span id="currentDate"></span>
        </div>
    </div>

    <!-- Results -->
    <div id="results"></div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- <script>
    $('#searchBtn').on('click', function() {
        let parent_name = $('#parent_name').val();

        $.get("<?= base_url('student/searchParent'); ?>", {
            parent_name: parent_name
        }, function(res) {
            let data = JSON.parse(res);

            if (data.error) {
                $('#results').html('<div class="alert alert-danger">' + data.error + '</div>');
                return;
            }

            let feeHeaders = data.fee_types || [];

            let html = `
    <h4>Guardian: ${data.parents[0].name}</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Student</th>
                <th>Registration No</th>
                <th>Class</th>
                <th>Section</th>
                ${feeHeaders.map(f => `<th>${f}</th>`).join('')}
                <th>Allocated Fee</th>
                <th>Paid Fee</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
`;

            data.students.forEach(stu => {
                html += `
        <tr>
            <td>${stu.first_name}</td>
            <td>${stu.register_no}</td>
            <td>${stu.class}</td>
            <td>${stu.section}</td>
            ${feeHeaders.map(f => `<td>${stu.fees[f] ?? '-'}</td>`).join('')}
            <td>${stu.allocated_amount ?? 0}</td>
            <td>${stu.paid ?? 0}</td>
            <td>${stu.balance ?? 0}</td>
        </tr>
    `;
            });

            html += `</tbody></table>`;
            $('#results').html(html);

        });
    });
</script> -->
<div id="results"></div>

<!-- Selected Students Table (Initially Hidden) -->
<div id="selectedStudents" style="margin-top:20px; display:none;">
    <h4>Collect Fees</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Student</th>
                <th>Reg No</th>
                <th>Class</th>
                <th>Section</th>
                <th>Admission Fee</th>
                <th>Library Fee</th>
                <th>Tuition Fee</th>
                <th>Total Allocated</th>
                <th>Enter Paid</th>
            </tr>
        </thead>
        <tbody id="selectedBody"></tbody>
    </table>
    <button class="btn btn-success" id="submitFees">Submit Fees</button>
</div>

<!-- <script>
$(function() {
    const monthMap = {
        1: 'January', 2: 'February', 3: 'March', 4: 'April',
        5: 'May', 6: 'June', 7: 'July', 8: 'August',
        9: 'September', 10: 'October', 11: 'November', 12: 'December'
    };

    // Search parent
    $('#searchBtn').on('click', function() {
        const parent_name = $('#parent_name').val().trim();
        if (!parent_name) {
            alert('Enter guardian name');
            return;
        }

        $.get("<?= base_url('student/searchParent'); ?>", { parent_name: parent_name }, function(res) {
            let data = res;
            if (typeof res === 'string') {
                try { data = JSON.parse(res); } 
                catch (e) { 
                    $('#results').html('<div class="alert alert-danger">Invalid server response.</div>'); 
                    return; 
                }
            }

            if (data.error) {
                $('#results').html('<div class="alert alert-danger">'+ data.error +'</div>');
                return;
            }

            if (!Array.isArray(data.students) || data.students.length === 0) {
                $('#results').html('<div class="alert alert-warning">No students found for this guardian.</div>');
                return;
            }

            const months = Array.isArray(data.months) ? data.months : [];
            let options = '<option value="">-- Select Month --</option>';
            months.forEach(m => {
                const mInt = parseInt(m);
                options += `<option value="${mInt}">${monthMap[mInt] || mInt}</option>`;
            });

            const html = `
                <div class="mb-2"><strong>Guardian:</strong> ${data.parents?.[0]?.name || ''}</div>
                <div id="readonlyTableContainer"></div>
                <div class="form-inline mb-3 mt-3">
                    <label class="mr-2">Choose Month:</label>
                    <select id="monthSelect" class="form-control">${options}</select>
                </div>
                <div id="editableTableContainer"></div>
                <input type="hidden" id="current_parent_id" value="${data.parents?.[0]?.id || ''}" />
            `;
            $('#results').html(html);

            // Render readonly table first
            renderReadonlyTable(data.students);

            // Month dropdown handler
            $('#monthSelect').on('change', function() {
                const selectedMonth = $(this).val();
                if (!selectedMonth) {
                    $('#editableTableContainer').empty();
                    return;
                }

                // Collect selected students
                const selected = [];
                $('.student-check:checked').each(function() {
                    selected.push($(this).data('student'));
                });

                if (selected.length === 0) {
                    alert("Please select at least one student from the first table.");
                    $(this).val("");
                    return;
                }

                renderEditableTable(selected, parseInt(selectedMonth));
            });
        });
    });

    // Non-editable table with checkboxes
    function renderReadonlyTable(students) {
        let feeHeaderMap = {};
        students.forEach(stu => {
            (stu.fees || []).forEach(f => {
                feeHeaderMap[f.fee_type_id] = f.fee_type;
            });
        });
        const feeHeaders = Object.keys(feeHeaderMap).map(id => ({ id: parseInt(id), name: feeHeaderMap[id] }));

        let html = `<table class="table table-bordered text-dark"><thead><tr>
            <th>Select</th><th>Student</th><th>Reg No</th><th>Class</th><th>Section</th>`;
        feeHeaders.forEach(h => html += `<th>${h.name}</th>`);
        html += `<th>Total Allocated</th><th>Paid</th><th>Balance</th></tr></thead><tbody>`;

        students.forEach(stu => {
            const feeMap = {};
            (stu.fees || []).forEach(f => feeMap[f.fee_type_id] = f.amount);

            html += `<tr>
                <td><input type="checkbox" class="student-check" data-student='${JSON.stringify(stu)}'></td>
                <td>${stu.first_name} ${stu.last_name || ''}</td>
                <td>${stu.register_no || ''}</td>
                <td>${stu.class || ''}</td>
                <td>${stu.section || ''}</td>`;

            feeHeaders.forEach(h => {
                const amt = feeMap[h.id] ?? '-';
                html += `<td>${amt}</td>`;
            });

            html += `<td>${stu.allocated_amount ?? 0}</td>
                     <td>${stu.paid ?? 0}</td>
                     <td>${stu.balance ?? 0}</td></tr>`;
        });

        html += `</tbody></table>`;
        $('#readonlyTableContainer').html(html);
    }

    // Editable table
    function renderEditableTable(selectedStudents, month) {
        let feeHeaderMap = {};
        selectedStudents.forEach(stu => {
            (stu.fees || []).forEach(f => {
                if (parseInt(f.month) === month) {
                    feeHeaderMap[f.fee_type_id] = f.fee_type;
                }
            });
        });
        const feeHeaders = Object.keys(feeHeaderMap).map(id => ({ id: parseInt(id), name: feeHeaderMap[id] }));

        let html = `<div id="editableTable"><h5>Collect Fees</h5>
            <form id="familyFeeForm">
            <table class="table table-bordered text-dark"><thead><tr>
                <th>Student</th><th>Reg No</th><th>Class</th><th>Section</th>`;
        feeHeaders.forEach(h => html += `<th>${h.name}</th>`);
        html += `<th>Total Allocated</th><th>Paid</th><th>Balance</th></tr></thead><tbody>`;

        selectedStudents.forEach(stu => {
            const feeMap = {};
            (stu.fees || []).forEach(f => {
                if (parseInt(f.month) === month) feeMap[f.fee_type_id] = f.amount;
            });

            html += `<tr>
                <td>${stu.first_name} ${stu.last_name || ''}</td>
                <td>${stu.register_no || ''}</td>
                <td>${stu.class || ''}</td>
                <td>${stu.section || ''}</td>`;

            feeHeaders.forEach(h => {
                const amt = feeMap[h.id] ?? '';
                html += `<td>
                    <input class="form-control collect-amount" type="number" min="0"
                        data-student-id="${stu.id}"
                        data-fee-type-id="${h.id}"
                        value="${amt}" />
                </td>`;
            });

            html += `<td>${stu.allocated_amount ?? 0}</td>
                     <td>${stu.paid ?? 0}</td>
                     <td>${stu.balance ?? 0}</td></tr>`;
        });

        html += `</tbody></table>
            <button type="button" id="submitFamilyFees" class="btn btn-success">Submit Fees</button>
            </form></div>`;

        $('#editableTableContainer').html(html);

        // Submit handler
        $('#submitFamilyFees').off().on('click', function() {
            const parentId = $('#current_parent_id').val();
            const payments = [];

            $('.collect-amount').each(function() {
                const amount = parseFloat($(this).val());
                if (!isNaN(amount) && amount > 0) {
                    payments.push({
                        student_id: $(this).data('student-id'),
                        fee_type_id: $(this).data('fee-type-id'),
                        amount: amount,
                        month: month
                    });
                }
            });

            if (payments.length === 0) {
                alert('Please enter at least one amount to collect.');
                return;
            }

            $.ajax({
                url: "<?= base_url('fees/submitFamilyFees'); ?>",
                method: "POST",
                contentType: "application/json",
                data: JSON.stringify({ parent_id: parentId, payments: payments }),
                success: function(res) {
                    let data = res;
                    if (typeof res === 'string') {
                        try { data = JSON.parse(res); } catch(e){ alert('Invalid server response'); return; }
                    }
                    if (data.status === 'success') {
                        alert('Fees collected successfully');
                        $('#searchBtn').trigger('click');
                    } else {
                        alert('Error: ' + (data.message || 'Could not collect fees'));
                    }
                }
            });
        });
    }
});
</script> -->
<script>
    var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';
</script>


<script>
    $(function() {
        const monthMap = {
            1: 'January',
            2: 'February',
            3: 'March',
            4: 'April',
            5: 'May',
            6: 'June',
            7: 'July',
            8: 'August',
            9: 'September',
            10: 'October',
            11: 'November',
            12: 'December'
        };

        // Search parent
        $('#searchBtn').on('click', function() {
            const parent_name = $('#parent_name').val().trim();
            if (!parent_name) {
                alert('Enter guardian name');
                return;
            }

            $.get("<?= base_url('student/searchParent'); ?>", {
                parent_name: parent_name
            }, function(res) {
                let data = res;
                if (typeof res === 'string') {
                    try {
                        data = JSON.parse(res);
                    } catch (e) {
                        $('#results').html('<div class="alert alert-danger">Invalid server response.</div>');
                        return;
                    }
                }

                if (data.error) {
                    $('#results').html('<div class="alert alert-danger">' + data.error + '</div>');
                    return;
                }

                if (!Array.isArray(data.students) || data.students.length === 0) {
                    $('#results').html('<div class="alert alert-warning">No students found for this guardian.</div>');
                    return;
                }

                const months = Array.isArray(data.months) ? data.months : [];
                const academicOrder = [4, 5, 6, 7, 8, 9, 10, 11, 12, 1, 2, 3];
                months.sort((a, b) => academicOrder.indexOf(parseInt(a)) - academicOrder.indexOf(parseInt(b)));
                let options = '<option value="">-- Select Month --</option>';
                months.forEach(m => {
                    const mInt = parseInt(m);
                    options += `<option value="${mInt}">${monthMap[mInt] || mInt}</option>`;
                });

                const html = `
                <div class="mb-2"><strong>Guardian:</strong> ${data.parents?.[0]?.name || ''}</div>
                <div id="readonlyTableContainer"></div>
                <div class="form-inline mb-3 mt-3">
                    <label class="mr-2">Choose Month:</label>
                    <select id="monthSelect" class="form-control">${options}</select>
                </div>
                <div id="editableTableContainer"></div>
                <input type="hidden" id="current_parent_id" value="${data.parents?.[0]?.id || ''}" />
            `;
                $('#results').html(html);

                renderReadonlyTable(data.students);

                // When month changes OR checkbox changes → auto render editable table
                $(document).on('change', '#monthSelect, .student-check', function() {
                    const month = parseInt($('#monthSelect').val());
                    if (!month) {
                        $('#editableTableContainer').empty();
                        return;
                    }

                    const selected = [];
                    $('.student-check:checked').each(function() {
                        selected.push($(this).data('student'));
                    });

                    if (selected.length === 0) {
                        $('#editableTableContainer').empty();
                        return;
                    }

                    renderEditableTable(selected, month);
                });
            });
        });

        // Non-editable table with checkboxes + fine
        function renderReadonlyTable(students) {
            let feeHeaderMap = {};
            students.forEach(stu => {
                (stu.fees || []).forEach(f => {
                    feeHeaderMap[f.fee_type_id] = f.fee_type;
                });
            });
            const feeHeaders = Object.keys(feeHeaderMap).map(id => ({
                id: parseInt(id),
                name: feeHeaderMap[id]
            }));

            let html = `<table class="table table-bordered text-dark"><thead><tr>
            <th>Select</th><th>Student</th><th>Reg No</th><th>Class</th><th>Section</th>`;
            feeHeaders.forEach(h => html += `<th>${h.name}</th>`);
            html += `<th>Fine</th><th>Total Allocated</th><th>Status</th><th>Balance</th></tr></thead><tbody>`;

            students.forEach(stu => {
                const feeMap = {};
                (stu.fees || []).forEach(f => feeMap[f.fee_type_id] = f.amount);

                html += `<tr>
                <td><input type="checkbox" class="student-check" data-student='${JSON.stringify(stu)}'></td>
                <td>${stu.first_name} ${stu.last_name || ''}</td>
                <td>${stu.register_no || ''}</td>
                <td>${stu.class || ''}</td>
                <td>${stu.section || ''}</td>`;

                feeHeaders.forEach(h => {
                    const amt = feeMap[h.id] ?? '-';
                    html += `<td>${amt}</td>`;
                });

                html += `<td>${stu.fine_calculated_amount ?? 0}</td>
                     <td>${stu.allocated_amount ?? 0}</td>
                     <td>${stu.paid ?? 0}</td>
                     <td>${stu.balance ?? 0}</td></tr>`;
            });

            html += `</tbody></table>`;
            $('#readonlyTableContainer').html(html);
        }

        // Editable table with fine + discount + grand total
        function renderEditableTable(selectedStudents, month) {
            let feeHeaderMap = {};
            selectedStudents.forEach(stu => {
                (stu.fees || []).forEach(f => {
                    if (parseInt(f.month) === month) {
                        feeHeaderMap[f.fee_type_id] = f.fee_type;
                    }
                });
            });
            const feeHeaders = Object.keys(feeHeaderMap).map(id => ({
                id: parseInt(id),
                name: feeHeaderMap[id]
            }));

            let html = `<div id="editableTable"><h5>Collect Fees</h5>
            <form id="familyFeeForm">
            <table class="table table-bordered text-dark"><thead><tr>
                <th>Student</th><th>Reg No</th><th>Class</th><th>Section</th>`;
            feeHeaders.forEach(h => html += `<th>${h.name}</th>`);
            html += `<th>Fine</th><th>Discount</th><th>Total Allocated</th><th>Paid</th><th>Balance</th></tr></thead><tbody>`;

            selectedStudents.forEach(stu => {
                const feeMap = {};
                (stu.fees || []).forEach(f => {
                    if (parseInt(f.month) === month) feeMap[f.fee_type_id] = f.amount;
                });

                html += `<tr>
                <td>${stu.first_name} ${stu.last_name || ''}</td>
                <td>${stu.register_no || ''}</td>
                <td>${stu.class || ''}</td>
                <td>${stu.section || ''}</td>`;

                feeHeaders.forEach(h => {
                    const amt = feeMap[h.id] ?? '';
                    html += `<td>
                    <input class="form-control collect-amount" type="number" min="0"
                        data-student-id="${stu.id}"
                        data-fee-type-id="${h.id}"
                        value="${amt}" />
                </td>`;
                });

                html += `<td>
                        <input class="form-control fine-amount" type="number" min="0"
                            data-student-id="${stu.id}"
                            data-field="fine_calculated_amount"
                            value="${stu.fine_calculated_amount ?? 0}" />
                     </td>
                     <td>
                        <input class="form-control discount-amount" type="number" min="0"
                            data-student-id="${stu.id}" value="0" />
                     </td>
                     <td>${stu.allocated_amount ?? 0}</td>
                     <td>${stu.paid ?? 0}</td>
                     <td>${stu.balance ?? 0}</td></tr>`;
            });

            html += `</tbody></table>

            <!-- Grand Total Row -->
            <div class="mt-3 mb-3">
                <strong>Total Fee:</strong> <span id="totalFee">0</span> |
                <strong>Total Fine:</strong> <span id="totalFine">0</span> |
                <strong>Total Discount:</strong> <span id="totalDiscount">0</span> |
                <strong>Net Total:</strong> <span id="netTotal">0</span>
            </div>

            <button type="button" id="submitFamilyFees" class="btn btn-success">Submit Fees</button>
            </form></div>`;

            $('#editableTableContainer').html(html);

            // Auto calculate totals
            function calculateTotals() {
                let totalFee = 0,
                    totalFine = 0,
                    totalDiscount = 0;
                $('.collect-amount').each(function() {
                    const v = parseFloat($(this).val());
                    if (!isNaN(v)) totalFee += v;
                });
                $('.fine-amount').each(function() {
                    const v = parseFloat($(this).val());
                    if (!isNaN(v)) totalFine += v;
                });
                $('.discount-amount').each(function() {
                    const v = parseFloat($(this).val());
                    if (!isNaN(v)) totalDiscount += v;
                });
                const netTotal = totalFee + totalFine - totalDiscount;
                $('#totalFee').text(totalFee.toFixed(2));
                $('#totalFine').text(totalFine.toFixed(2));
                $('#totalDiscount').text(totalDiscount.toFixed(2));
                $('#netTotal').text(netTotal.toFixed(2));
            }

            $(document).on('input', '.collect-amount, .fine-amount, .discount-amount', calculateTotals);
            calculateTotals();

            // Submit handler
            $('#submitFamilyFees').off().on('click', function() {
                const parentId = $('#current_parent_id').val();
                const payments = [];

                $('.collect-amount').each(function() {
                    const amount = parseFloat($(this).val());
                    if (!isNaN(amount) && amount > 0) {
                        payments.push({
                            student_id: $(this).data('student-id'),
                            fee_type_id: $(this).data('fee-type-id'),
                            amount: amount,
                            month: month
                        });
                    }
                });

                $('.fine-amount').each(function() {
                    const fine = parseFloat($(this).val());
                    if (!isNaN(fine) && fine > 0) {
                        payments.push({
                            student_id: $(this).data('student-id'),
                            field: 'fine_calculated_amount',
                            amount: fine,
                            month: month
                        });
                    }
                });

                $('.discount-amount').each(function() {
                    const discount = parseFloat($(this).val());
                    if (!isNaN(discount) && discount > 0) {
                        payments.push({
                            student_id: $(this).data('student-id'),
                            field: 'discount',
                            amount: discount,
                            month: month
                        });
                    }
                });

                if (payments.length === 0) {
                    alert('Please enter at least one amount to collect.');
                    return;
                }

                $.ajax({
                    url: "<?= base_url('fees/submitFamilyFees'); ?>",
                    type: "POST",
                    data: {
                        parent_id: parentId,
                        payments: JSON.stringify(payments), // stringify here
                        '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
                    },
                    dataType: "json",
                    success: function(res) {
                        if (res.status === 'success') {
                            alert('Fees collected successfully');
                            $('#searchBtn').trigger('click');
                        } else {
                            alert('Error: ' + (res.message || 'Could not collect fees'));
                        }
                    }
                });
            });
        }
    });
</script>



<script>
    $(function() {
        function getCurrentDate() {
            const today = new Date();
            return today.toLocaleDateString('en-GB'); // format: dd/mm/yyyy
        }

        // show current date immediately on page load
        $('#currentDate').text(getCurrentDate());

        // ... your existing JS code here (search, tables, etc.) ...
    });
</script>