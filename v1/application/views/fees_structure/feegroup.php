
    <div class="fees-page">
  <div class="fees-card add-fees">
    <h2>Add Fees Group</h2>

    <div class="form-group">
  <label>Name <span class="required">*</span></label>
  <input type="text" class="form-control" placeholder="Enter name">
</div>

<div class="form-group">
  <label>Description</label>
  <textarea class="form-control" rows="4" placeholder="Enter description"></textarea>
</div>


    <button class="save-btn">Save</button>
  </div>

  <div class="fees-card fees-list">
    <h2>Fees Group List</h2>
    <input type="text" class="search-input" placeholder="Search...">

    <table class="fees-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Description</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1st to 5th</td>
          <td>Test Description</td>
          <td>
  <a href="#" title="Edit">
    <img src="<?= asset_url('assets/images/edit.png') ?>" alt="Edit" class="action-icon" width="20" height="20">
  </a>
  <a href="#" title="Delete">
    <img src="<?= asset_url('assets/images/bin.png') ?>" alt="Delete" class="action-icon" width="20" height="20">
  </a>
</td>

        </tr>
        <!-- Repeat similar rows -->
      </tbody>
    </table>
  </div>
</div>
