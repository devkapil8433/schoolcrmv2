<!-- Filter Form in a Card -->
<div class="custom-card">
  <h5>Select Criteria</h5>
  <form method="get" id="filterForm">
    <div class="custom-row">
      <div class="custom-col custom-form-group">
        <label for="class_id">Class <span style="color:red">*</span></label>
        <select name="class_id" id="class_id" required>
          <option value="">Select</option>
          <option>Class 1st</option>
          <option>Class 2nd</option>
          <option>Class 3rd</option>
          <!-- Add more classes -->
        </select>
      </div>

      <div class="custom-col custom-form-group">
        <label for="section_id">Section</label>
        <select name="section_id" id="section_id">
          <option value="">Select</option>
        </select>
      </div>

      <div class="custom-col custom-form-group">
        <label for="keyword">Search By Keyword</label>
        <input type="text" name="keyword" id="keyword" placeholder="Name, Roll, Enroll, etc.">
      </div>
    </div>

    <div>
      <button type="submit" class="custom-btn-search">
        🔍 Search
      </button>
    </div>
  </form>
  
  <!-- Leave your DataTable untouched -->
  <div class="row datatable" style="margin-top: 20px;">
    <table id="datatable" class="display" style="width:100%">
      <thead>
        <tr>
          <th>#</th>
          <th>Class</th>
          <th>Section</th>
          <th>Admission No</th>
          <th>Student Name</th>
          <th>Father Name</th>
          <th>Date of Birth</th>
          <th>Phone</th>
          <th></th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>


