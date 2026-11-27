<?php $currency_symbol = $global_config['currency_symbol']; ?>
<section class="panel">
    <header class="panel-heading">
        <h4 class="panel-title">Edit Set</h4>
    </header>

    <?php echo form_open('inventory/update_set/' . $set['id'], ['id' => 'frmSubmit']); ?>

    <div class="panel-body">

        <div class="form-group">
            <label>Set Name</label>
            <input type="text"
                class="form-control"
                name="set_name"
                value="<?= html_escape($set['set_name']) ?>"
                required>
        </div>

        <div class="form-group">
            <label> Branch</label>
            <div class="col-md-12">
                <?php
                echo form_dropdown(
                    'branch_id',
                    $branchlist,
                    $set['branch_id'],
                    "class='form-control' id='branchID' data-plugin-selectTwo data-width='100%'"
                );
                ?>
            </div>
        </div>


        <div class="form-group">
            <label>Class</label>
            <?php
            echo form_dropdown(
                'class_id',
                $this->app_lib->getClass($branch_id),
                $set['class_id'],
                "class='form-control' data-plugin-selectTwo"
            );
            ?>
        </div>

        <hr>

        <table class="table table-bordered" id="editTable">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Product</th>
                    <th>Unit Price</th>
                    <th>Qty</th>
                    <th>Discount</th>
                    <th>Total</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($set_items as $i => $item): ?>
                    <?php
                    $unit_price = (float)$item['unit_price'];
                    $qty        = (float)$item['quantity'];
                    $discount   = (float)$item['discount'];
                    $sub_total  = $unit_price * $qty;
                    $net_total  = $sub_total - $discount;
                    ?>
                    <tr id="row_<?= $i ?>">

                        <td>
                            <?= form_dropdown(
                                "sales[$i][category]",
                                $categorylist,
                                $item['category_id'],
                                "class='form-control category'
             id='category$i'
             onchange='getProductByCategory(this.value, $i)'"
                            ); ?>
                        </td>

                        <td>
                            <select class="form-control sale_product"
                                name="sales[<?= $i ?>][product]"
                                id="product<?= $i ?>"
                                data-selected="<?= $item['product_id'] ?>">
                                <option value="">Loading...</option>
                            </select>
                        </td>

                        <td>
                            <input type="text"
                                class="form-control purchase_unit_price"
                                name="sales[<?= $i ?>][unit_price]"
                                value="<?= $item['unit_price'] ?>"
                                readonly>
                        </td>

                        <td>
                            <input type="number"
                                class="form-control purchase_quantity"
                                name="sales[<?= $i ?>][quantity]"
                                value="<?= $item['quantity'] ?>">
                        </td>

                        <td>
                            <input type="number"
                                class="form-control sale_discount"
                                name="sales[<?= $i ?>][discount]"
                                value="<?= $item['discount'] ?>">
                        </td>

                        <td class="min-w-md">
                            <input type="text"
                                class="form-control net_sub_total"
                                name="sales[<?= $i ?>][net_sub_total]"
                                value="<?= number_format($net_total, 2) ?>"
                                readonly>

                            <input type="hidden"
                                class="sub_total"
                                name="sales[<?= $i ?>][sub_total]"
                                value="<?= number_format($sub_total, 2) ?>">
                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>


        </table>

    </div>

    <footer class="panel-footer text-right">
        <button type="submit" class="btn btn-primary">
            Update Set
        </button>
        <a href="<?= base_url('inventory/set') ?>" class="btn btn-default">
            Cancel
        </a>
    </footer>

    <?php echo form_close(); ?>
</section>
<script>
    $(document).ready(function() {

        $('.category').each(function() {
            let row = $(this).data('row');
            let categoryId = $(this).val();
            let productSelect = $('.product[data-row="' + row + '"]');
            let selectedProduct = productSelect.data('selected');

            if (categoryId) {
                loadProducts(categoryId, productSelect, selectedProduct);
            }
        });

        $('.category').on('change', function() {
            let row = $(this).data('row');
            let categoryId = $(this).val();
            let productSelect = $('.product[data-row="' + row + '"]');

            loadProducts(categoryId, productSelect, null);
        });

    });

    function loadProducts(categoryId, productSelect, selectedProduct) {
        $.ajax({
            url: "<?= base_url('inventory/getProductsByCategory') ?>",
            type: "POST",
            data: {
                category_id: categoryId
            },
            dataType: "json",
            success: function(res) {
                productSelect.empty();
                productSelect.append('<option value="">Select Product</option>');

                $.each(res, function(i, item) {
                    let selected = (item.id == selectedProduct) ? 'selected' : '';
                    productSelect.append(
                        `<option value="${item.id}" ${selected}>${item.name}</option>`
                    );
                });
            }
        });
    }
</script>


<?php if (!empty($branch_id)) { ?>
    <script type="text/javascript">
        function getDynamicInput(value) {
            var html_row = "";
            html_row += '<tr id="row_' + value + '">';
            html_row += '<td><div class="form-group">';
            html_row += '<select id="category' + value + '" name="sales[' + value + '][category]" class="form-control select2_in"  onchange="getProductByCategory(this.value, ' + value + ')" >';
            html_row += '<option value=""><?php echo translate('select'); ?></option>';
            <?php
            $categorylist = $this->app_lib->getTable('product_category');
            foreach ($categorylist as $category):
            ?>
                html_row += '<option value="<?php echo html_escape($category['id']) ?>"><?php echo html_escape($category['name']); ?></option>';
            <?php endforeach; ?>
            html_row += '</select>';
            html_row += '<span class="error"></span></div></td>';
            html_row += '<td><div class="form-group">';
            html_row += '<select id="product' + value + '" name="sales[' + value + '][product]" class="form-control sale_product" >';
            html_row += '<option value=""><?php echo translate('select'); ?></option>';
            html_row += '</select>';
            html_row += '<span class="error"></span></div></td>';
            html_row += '</div></td>';
            html_row += '<td><div class="form-group">';
            html_row += '<input type="text" name="sales[' + value + '][unit_price]" class="form-control purchase_unit_price" readonly value="0.00" />';
            html_row += '</div></td>';
            html_row += '<td><div class="form-group">';
            html_row += '<div class="input-group">';
            html_row += '<input id="quantity' + value + '" type="number" name="sales[' + value + '][quantity]" class="form-control purchase_quantity" autocomplete="off" value="1" />';
            html_row += '<span class="input-group-addon">-</span></div>';
            html_row += '<span class="quantity_remain"></span>';
            html_row += '<span class="error"></span></div></td>';
            html_row += '</div></td>';
            html_row += '<td><div class="form-group">';
            html_row += '<input type="number" name="sales[' + value + '][discount]" class="form-control sale_discount" value="0" />';
            html_row += '</div></td>';
            html_row += '<td class="min-w-md">';
            html_row += '<input type="text" class="form-control net_sub_total" name="sales[' + value + '][net_sub_total]" value="0.00" readonly style="float: left; width: 70%;" />';
            html_row += '<input type="hidden" class="sub_total" name="sales[' + value + '][sub_total]" value="0" />';
            html_row += '<button type="button" class="btn btn-danger" onclick="deleteRow(' + value + ')" style="float: right; max-width: 30%"><i class="fas fa-times"></i> </button>';
            html_row += '</td>';
            html_row += '</tr>';
            return html_row;
        }
    </script>
<?php } ?>

<script type="text/javascript">
    var cusDataTable = '';
    $(document).ready(function() {
        cusDataTable = initDatatable('#invSalesList', 'inventory/getSaleslistDT');
        $(document).on('change', '#branchID', function() {
            var branchID = $(this).val();
            getClassByBranch(branchID);
            $('#roleID').val('').trigger('change.select2');
            $('#receiverID').empty().html("<option value=''><?= translate('select_user') ?>");


            $.ajax({
                url: "<?= base_url('inventory/saleItems') ?>",
                type: 'POST',
                data: {
                    branch_id: branchID
                },
                success: function(data) {
                    $('#purchaseItems').html(data).slideDown();
                    $(".selectTwo").each(function() {
                        var $this = $(this);
                        $this.themePluginSelect2({});
                    });
                }
            });
        });
        $(document).on('change', '#roleID', function() {
            var roleID = $(this).val();
            var branchID = $('#branchID').val();
            if (roleID == 6) {
                $.ajax({
                    url: base_url + "communication/getParentListBranch",
                    type: 'POST',
                    data: {
                        branch_id: branchID
                    },
                    success: function(data) {
                        $('#receiverID').html(data);
                    }
                });
                $(".class_div").hide(400);
            } else if (roleID == 7) {
                $(".class_div").show(400);;
                $('#receiverID').empty().html("<option value=''><?= translate('select_user') ?>");
            } else {
                $(".class_div").hide(400);
                $.ajax({
                    url: base_url + "communication/getStafflistRole",
                    type: 'POST',
                    data: {
                        branch_id: branchID,
                        role_id: roleID
                    },
                    success: function(data) {
                        $('#receiverID').html(data);
                    }
                });
            }
        });

        $(document).on('change', '#class_id', function() {
            var classID = $(this).val();
            var branchID = $('#branchID').val();
            $.ajax({
                url: base_url + "communication/getStudentByClass",
                type: 'POST',
                data: {
                    branch_id: branchID,
                    class_id: classID
                },
                success: function(data) {
                    $('#receiverID').html(data);
                }
            });
        });
    });

    var count = 1;
    $(document).ready(function() {
        $(document).on('change', '.sale_product', function() {
            var row = $(this).closest('tr');
            var id = $(this).val();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    'id': id
                },
                url: "<?php echo base_url('inventory/getSaleprice'); ?>",
                success: function(result) {
                    row.find('.quantity_remain').html(result.availablestock);
                    row.find('.input-group-addon').html(result.unit);
                    var unit_price = read_number(result.price);
                    var quantity = read_number(row.find('.purchase_quantity').val());
                    var discount = read_number(row.find('.sale_discount').val());
                    var total_price = unit_price * quantity;
                    row.find('.purchase_unit_price').val(unit_price.toFixed(2));
                    var after_discount = total_price - discount;
                    row.find('.sub_total').val(total_price.toFixed(2));
                    row.find('.net_sub_total').val(after_discount.toFixed(2));
                    grandTotalCalculatePur();
                }
            });
        });

        $(document).on('change keyup', '.purchase_quantity, .sale_discount', function() {
            var row = $(this).closest('tr');
            var quantity = read_number(row.find('.purchase_quantity').val());
            var unit_price = read_number(row.find('.purchase_unit_price').val());
            var discount = read_number(row.find('.sale_discount').val());
            var total_price = unit_price * quantity;
            var after_discount = total_price - discount;
            row.find('.sub_total').val(total_price.toFixed(2));
            row.find('.net_sub_total').val(after_discount.toFixed(2));
            grandTotalCalculatePur();
        });
    });

    function addRows() {
        var tbody = $('#tableID').children('tbody');
        tbody.append(getDynamicInput(count));
        $(`#product${count}, .select2_in`).select2({
            theme: "bootstrap",
            width: "100%"
        });
        count++;
    }

    function deleteRow(id) {
        $("#row_" + id).remove();
        grandTotalCalculatePur();
    }

    // inventory product purchase grand total calculation
    function grandTotalCalculatePur() {
        var netGrandTotal = 0;
        $(".net_sub_total").each(function() {
            netGrandTotal += read_number(this.value)
        });

        $("#netPayable").val(netGrandTotal.toFixed(2));
        $("#netGrandTotal").val(netGrandTotal.toFixed(2));

        var grandTotal = 0;
        $(".sub_total").each(function() {
            grandTotal += read_number(this.value)
        });
        $("#grandTotal").val(grandTotal.toFixed(2));
        $("#sub_total_amount").val(grandTotal.toFixed(2));

        var total_discount = 0;
        $(".sale_discount").each(function() {
            total_discount += read_number(this.value)
        });
        $("#total_discount").val(total_discount);
        $("#totalDiscount").val(total_discount);
    }

    function getProductByCategory(category_id, row_id) {

        var branchID = $('#branchID').val();
        var selectedProduct = $('#product' + row_id).data('selected');

        $("#product" + row_id).html("<option>Loading...</option>");

        $.ajax({
            type: "POST",
            url: base_url + "inventory/getProductByCategory",
            data: {
                branch_id: branchID,
                category_id: category_id,
                selected_id: selectedProduct
            },
            dataType: "html",
            success: function(data) {
                $("#product" + row_id).html(data);
            }
        });
    }


    $("#frmSubmit").on('submit', (function(e) {
        e.preventDefault();
        var btn = $("#savebtn");
        btn.button('loading');
        $.ajax({
            url: $(this).attr('action'),
            type: "POST",
            data: $(this).serialize(),
            dataType: 'json',
            success: function(data) {
                if (data.status == "fail") {
                    console.log(data.error);
                    $.each(data.error, function(index, value) {
                        $('#' + index).parents('.form-group').find('.error').html(value);
                    });
                    btn.button('reset');
                } else {
                    window.location.href = data.url;
                }
            },
            error: function() {
                //  alert("Fail")
            }
        });
    }));

    function deleteSet(id) {
        if (!confirm('Are you sure you want to delete this set?')) {
            return;
        }

        $.ajax({
            url: base_url + 'inventory/delete_set/' + id,
            type: 'POST',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    alert(res.message);
                    location.reload();
                } else {
                    alert(res.message);
                }
            },
            error: function() {
                alert('Something went wrong');
            }
        });
    }
</script>
<script>
    $(document).ready(function() {
        <?php foreach ($set_items as $i => $item): ?>
            getProductByCategory(<?= $item['category_id'] ?>, <?= $i ?>);
        <?php endforeach; ?>
    });
</script>