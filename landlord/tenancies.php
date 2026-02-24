<?php
require  ("headd.php");
// --- Fetch Tenancies ---
$sql = "SELECT 
            tenancies.id AS tenancy_id,
            tenants.name AS tenant_name,
            tenants.email AS tenant_email,
            tenants.phone AS tenant_phone,
            tenants.id_number AS tenant_id_number,
            tenants.next_of_kin_name,
            tenants.next_of_kin_phone,
            tenants.address AS tenant_address,
            houses.house_number,
            houses.location,
            tenancies.rent_amount,
            tenancies.move_in_date,
            tenancies.move_out_date,
            tenancies.status
        FROM tenancies
        JOIN tenants ON tenancies.tenant_id = tenants.id
        JOIN houses ON tenancies.house_id = houses.id
        ORDER BY tenancies.id DESC";

$result = $conn->query($sql);
?>

<style>
    /* Action column */
#example th:nth-child(15),
#example td:nth-child(15) {
    min-width: 80px;
    text-align: center;
}

/* Move In column */
#example th:nth-child(12),
#example td:nth-child(12) {
    min-width: 80px;
}

/* Move Out column */
#example th:nth-child(13),
#example td:nth-child(13) {
    min-width: 80px;
}

</style>

<div class="container-fluid mt-4">

    <div class="content-body">
          <div class="row page-titles mx-0">
            <h2>Tenancy List</h2>
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Hi, welcome back!</h4>
                            <span class="ml-1">Tenancies List</span>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Tenants</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Tenancies</a></li>
                        </ol>
                    </div>
                </div>
        <div class="container-fluid">

           

            <div class="row">

            

            <!-- MOVE IN -->
            <div class="col-xl-6 col-xxl-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Tenant Move-In</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form id="moveInForm">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label style="color: #333333;">Select House</label>
                                        <select id="movein_select_house" class="form-control" required>
                                            <option value="">Select House</option>
                                            <?php
                                            $houses = $conn->query("SELECT id, house_number FROM houses WHERE landlord_id=".$_SESSION['user_id']." AND status='vacant'");
                                            while($row = $houses->fetch_assoc()){
                                                echo "<option value='{$row['id']}'>{$row['house_number']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label style="color: #333333;">Select Tenant</label>
                                        <select id="movein_select_tenant" class="form-control" required>
                                            <option value="">Select Tenant</option>
                                            <?php
                                            $tenants = $conn->query("SELECT t.id, u.name FROM tenants t JOIN users u ON t.user_id=u.id");
                                            while($t = $tenants->fetch_assoc()){
                                                echo "<option value='{$t['id']}'>{$t['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label style="color: #333333;">Rent Amount</label>
                                        <input type="number" id="movein_rent_amount" class="form-control" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label style="color: #333333;">Move-In Date</label>
                                        <input type="date" id="movein_date" class="form-control" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-info">Move In Tenant</button>
                            </form>

                            <div id="moveInMessage" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>

                <div class="col-12">
                    <div class="card">
                         <h4 class="card-title">Tenancies Table</h4>
                        <div class="card-header">
                           
                        </div>
                        <div class="card-body">
                                <table id="example" class="display" style="min-width: 845px">
                                    <thead>
                                        <tr>
                                             <th>ID</th>
                                             <th>Tenant Name</th>
                                             <th>Email</th>
                                             <th>Phone</th>
                                             <th>ID Number</th>
                                             <th>Next of Kin</th>
                                             <th>Next of Kin Phone</th>
                                             <th>Address</th>
                                             <th>House Number</th>
                                             <th>Location</th>
                                             <th>Rent Amount</th>
                                             <th>Move In</th>
                                             <th>Move Out</th>
                                             <th>Status</th>
                                             <th>Action</th>
                                             <th>Action</th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                        <?php if($result->num_rows > 0): ?>
                                            <?php while($row = $result->fetch_assoc()): ?>
                                                <tr style="color: #313654;">
                                                    <td><?= $row['tenancy_id'] ?></td>
                                                    <td><?= htmlspecialchars($row['tenant_name']) ?></td>
                                                    <td><?= htmlspecialchars($row['tenant_email']) ?></td>
                                                    <td><?= htmlspecialchars($row['tenant_phone']) ?></td>
                                                    <td><?= htmlspecialchars($row['tenant_id_number']) ?></td>
                                                    <td><?= htmlspecialchars($row['next_of_kin_name']) ?></td>
                                                    <td><?= htmlspecialchars($row['next_of_kin_phone']) ?></td>
                                                    <td><?= htmlspecialchars($row['tenant_address']) ?></td>
                                                    <td><?= htmlspecialchars($row['house_number']) ?></td>
                                                    <td><?= htmlspecialchars($row['location']) ?></td>
                                                    <td><?= htmlspecialchars($row['rent_amount']) ?></td>
                                                    <td><?= htmlspecialchars($row['move_in_date']) ?></td>
                                                    <td><?= htmlspecialchars($row['move_out_date']) ?></td>
                                                   <td>
                                                        <?php if ($row['status'] === 'active'): ?>
                                                            <span class="btn btn-success btn-sm">Active</span>
                                                        <?php else: ?>
                                                            <span class="btn btn-danger btn-sm">Ended</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                       <button class="btn btn-primary btn-sm edit-btn"
                                                                data-id="<?= $row['tenancy_id'] ?>"
                                                                data-rent="<?= $row['rent_amount'] ?>"
                                                                data-status="<?= $row['status'] ?>"
                                                                data-moveout="<?= $row['move_out_date'] ?>">
                                                            Update
                                                        </button>

                                                        </td>
                                                        <td>

                                                                                                 
                                                        <button class="btn btn-danger btn-sm delete-btn"
                                                                data-id="<?= $row['tenancy_id'] ?>">
                                                            Delete
                                                        </button>

                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr><td colspan="14" style="text-align:center;">No tenancies found</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                     <tfoot>
                                        <tr>
                                             <th>ID</th>
                                             <th>Tenant Name</th>
                                             <th>Email</th>
                                             <th>Phone</th>
                                             <th>ID Number</th>
                                             <th>Next of Kin</th>
                                             <th>Next of Kin Phone</th>
                                             <th>Address</th>
                                             <th>House Number</th>
                                             <th>Location</th>
                                             <th>Rent Amount</th>
                                             <th>Move In</th>
                                             <th>Move Out</th>
                                             <th>Status</th>
                                             <th>Action</th>
                                             <th>Action</th>
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




                                     <div class="modal fade" id="editTenancyModal" tabindex="-1">
                                  <div class="modal-dialog">
                                    <div class="modal-content">
                                      <form id="editTenancyForm">
                                        <div class="modal-header">
                                          <h5 class="modal-title">Update Tenancy</h5>
                                          <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                                                        
                                        <div class="modal-body">
                                        
                                          <input type="hidden" id="tenancy_id" name="id">
                                                                        
                                          <div class="form-group">
                                            <label>Rent Amount</label>
                                            <input type="number" class="form-control" id="rent_amount"  name="rent_amount" required>
                                          </div>
                                                                        
                                          <div class="form-group">
                                            <label>Status</label>
                                            <select class="form-control" id="status" name="status">
                                              <option value="active">Active</option>
                                              <option value="ended">Ended</option>
                                            </select>
                                          </div>
                                                                        
                                          <div class="form-group">
                                            <label>Move Out Date</label>
                                            <input type="date" class="form-control" id="move_out_date" name="move_out_date">
                                          </div>
                                        </div>
                                                                        
                                        <div class="modal-footer">
                                          <button type="submit" class="btn btn-success">Save Changes</button>
                                        </div>
                                      </form>
                                    </div>
                                  </div>
                                </div>
           

                                <?php $conn->close(); ?>
                                

 <script>
$("#editTenancyForm").on("submit", function (e) {
    e.preventDefault();

    $.ajax({
        url: "../ajax/update_tenancy.php", // 🔥 FIXED
        type: "POST",
        data: $(this).serialize(),
        dataType: "json",
        success: function (response) {
            if (response.status === "success") {
                location.reload();
            } else {
                alert(response.message);
            }
        },
        error: function (xhr) {
            alert("AJAX ERROR:\n" + xhr.responseText);
        }
    });
});

</script>

<script>
$(document).ready(function () {
    $('#example').DataTable({
        scrollX: true,
        autoWidth: false
    });
});
</script>


<?php
require("foott.php");

?>






