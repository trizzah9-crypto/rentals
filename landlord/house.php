<?php
require("headd.php");
?>

<div class="content-body">
    <div class="container-fluid">

        <!-- Page title -->
        <div class="row page-titles mx-0">
            <div class="col-sm-6 p-md-0">
                <div class="welcome-text">
                    <h4>Landlord Dashboard</h4>
                    <span class="ml-1">Management</span>
                </div>
            </div>
        </div>

        <!-- MAIN ROW -->
        <div class="row">

            <!-- ADD HOUSE -->
            <div class="col-xl-6 col-xxl-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Add House</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form id="addHouseForm">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label style="color: #333333;">House Number</label>
                                        <input type="text" id="house_number" class="form-control" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label style="color: #333333;">Location</label>
                                        <input type="text" id="location" class="form-control" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label style="color: #333333;">Rent Amount</label>
                                    <input type="number" id="rent_amount" class="form-control" required>
                                </div>

                                <button type="submit" class="btn btn-primary">Add House</button>
                            </form>

                            <div id="message" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>

         <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Basic Datatable</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example" class="display" style="min-width: 845px">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>House Number</th>
                                            <th>Location</th>
                                            <th>Rent (KES)</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody style="color: #333333;">
                                        <?php
                                        $sql = "SELECT * FROM houses ORDER BY id DESC";
                                        $result = mysqli_query($conn, $sql);

                                        if($result && mysqli_num_rows($result) > 0){
                                            $count = 1;
                                            while($row = mysqli_fetch_assoc($result)){
                                                $statusClass = $row['status'] === 'occupied' ? 'badge-danger' : 'badge-success';
                                                ?>
                                                <tr>
                                                    <td><?= $count++; ?></td>
                                                    <td><?= htmlspecialchars($row['house_number']); ?></td>
                                                    <td><?= htmlspecialchars($row['location']); ?></td>
                                                    <td><?= number_format($row['rent_amount']); ?></td>
                                                    <td>
                                                        <span class="badge <?= $statusClass ?>">
                                                            <?= ucfirst($row['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="table-actions">
                                                            <button class="btn btn-sm btn-primary edit-btn" data-id="<?= $row['id'] ?>" title="Edit">
                                                                <i class="bi bi-pencil"></i> Edit
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="<?= $row['id'] ?>" title="Delete">
                                                                <i class="bi bi-trash"></i> Delete
                                                            </button>
                                                        </td>
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            echo "<tr><td colspan='6' class='text-center'>No houses found</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>#</th>
                                            <th>House Number</th>
                                            <th>Location</th>
                                            <th>Rent (KES)</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

        </div>
    </div>
</div>



<div class="modal fade" id="editHouseModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editHouseForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit House</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="edit_house_id" name="house_id">
            <div class="mb-3">
                <label>House Number</label>
                <input type="text" id="edit_house_number" name="house_number" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Location</label>
                <input type="text" id="edit_location" name="location" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Rent Amount</label>
                <input type="number" id="edit_rent" name="rent_amount" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Status</label>
                <select id="edit_status" name="status" class="form-control">
                    <option value="vacant">Vacant</option>
                    <option value="occupied">Occupied</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Update</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Edit Button Click
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            fetch(`get_house.php?id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success'){
                        document.getElementById('edit_house_id').value = data.house.id;
                        document.getElementById('edit_house_number').value = data.house.house_number;
                        document.getElementById('edit_location').value = data.house.location;
                        document.getElementById('edit_rent').value = data.house.rent_amount;
                        document.getElementById('edit_status').value = data.house.status;
                        new bootstrap.Modal(document.getElementById('editHouseModal')).show();
                    } else {
                        alert(data.message);
                    }
                });
        });
    });

    // Submit Edit Form
    document.getElementById('editHouseForm').addEventListener('submit', e => {
        e.preventDefault();
        const formData = new FormData(e.target);
        fetch('update_house.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if(data.status === 'success'){
                location.reload();
            }
        });
    });

    // Delete Button Click
   document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault(); // <<-- prevent form submission / page reload

        if(confirm('Are you sure you want to delete this house?')){
            const id = btn.dataset.id;
            let formData = new FormData();
            formData.append('house_id', id);

            fetch('delete_house.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if(data.status === 'success'){
                    location.reload();
                }
            });
        }
    });
});

});
</script>


<?php
require("foott.php");
?>