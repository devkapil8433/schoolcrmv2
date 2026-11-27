<tbody>
<?php foreach ($sale_items as $i => $item): ?>
<tr id="row_<?= $i ?>">
  <td>
    <?php
    echo form_dropdown(
      "sales[$i][category]",
      $categorylist,
      $item->category_id,
      "class='form-control sale_category'
       onchange='getProductByCategory(this.value, $i, {$item->product_id})'"
    );
    ?>
  </td>

  <td>
    <select name="sales[<?= $i ?>][product]"
            id="product<?= $i ?>"
            class="form-control sale_product"></select>
  </td>

  <td>
    <input type="text"
           name="sales[<?= $i ?>][unit_price]"
           value="<?= $item->unit_price ?>"
           class="form-control purchase_unit_price" readonly />
  </td>

  <td>
    <input type="number"
           name="sales[<?= $i ?>][quantity]"
           value="<?= $item->quantity ?>"
           class="form-control purchase_quantity" />
  </td>

  <td>
    <input type="number"
           name="sales[<?= $i ?>][discount]"
           value="<?= $item->discount ?>"
           class="form-control sale_discount" />
  </td>

  <td>
    <input type="text"
           name="sales[<?= $i ?>][net_sub_total]"
           value="<?= $item->net_sub_total ?>"
           class="form-control net_sub_total" readonly />
  </td>
</tr>
<?php endforeach; ?>
</tbody>
