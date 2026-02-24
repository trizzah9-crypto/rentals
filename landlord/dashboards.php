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

                                <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Rent Due Day (1 – 28)</label>
                                            <input 
                                                type="number" 
                                                id="due_day" 
                                                class="form-control" 
                                                min="1" 
                                                max="28" 
                                                required
                                            >
                                        </div>
                                    
                                        <div class="form-group col-md-6">
                                            <label>Late Interest Per Day (KES)</label>
                                            <input 
                                                type="number" 
                                                id="late_interest_per_day" 
                                                class="form-control" 
                                                value="0" 
                                                required
                                            >
                                        </div>
                                    </div>
                                    

                                <button type="submit" class="btn btn-primary">Add House</button>
                            </form>

                            <div id="message" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>

          <!-- ADD TENANT -->
                        <div class="col-xl-6 col-xxl-12">
                           <div class="card">
                               <div class="card-header">
                                   <h4 class="card-title">Add Tenant</h4>
                               </div>
                               <div class="card-body">
                                   <div class="basic-form">
                                      <form id="addTenantForm">

                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Tenant Name</label>
                                                <input type="text" id="tenant_name" class="form-control" required>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Email</label>
                                                <input type="email" id="tenant_email" class="form-control" required>
                                            </div>
                                        </div>
                                    
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Phone</label>
                                                <input type="text" id="tenant_phone" class="form-control" required>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>ID Number</label>
                                                <input type="text" id="tenant_id_number" class="form-control" required>
                                            </div>
                                        </div>
                                    
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Username</label>
                                                <input type="text" id="tenant_username" class="form-control" required>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Temporary Password</label>
                                                <input type="text" id="tenant_password" class="form-control" required>
                                            </div>
                                        </div>
                                    
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Next of Kin Name</label>
                                                <input type="text" id="tenant_next_of_kin_name" class="form-control" required>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Next of Kin Phone</label>
                                                <input type="text" id="tenant_next_of_kin_phone" class="form-control" required>
                                            </div>
                                        </div>
                                    
                                        <div class="form-group">
                                            <label>Address</label>
                                            <input type="text" id="tenant_address" class="form-control">
                                        </div>
                                    
                                        <button type="submit" class="btn btn-success">Add Tenant</button>
                                    </form>
                                    

                                       <div id="tenantMessage" class="mt-3"></div>
                                   </div>
                               </div>
                           </div>
                        </div>


         <!-- MOVE IN -->
                        <div class="col-xl-6 col-xxl-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Tenant Move-In</h4>
                                </div>
                                <div class="card-body">
                                    <div class="basic-form">
                                        <form id="moveInForm">
                        
                                            <!-- Select House + Tenant -->
                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>Select House</label>
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
                                                           <label>Select Tenant</label>
                                                           <select id="movein_select_tenant" class="form-control" required>
                                                               <option value="">Select Tenant</option>
                                                               <?php
                                                               $tenants = $conn->query("
                                                                   SELECT t.id, u.name
                                                                   FROM tenants t
                                                                   JOIN users u ON t.user_id = u.id
                                                                   WHERE t.id NOT IN (
                                                                       SELECT tenant_id FROM tenancies
                                                                   )
                                                               ");

                                                               while ($t = $tenants->fetch_assoc()) {
                                                                   echo "<option value='{$t['id']}'>{$t['name']}</option>";
                                                               }
                                                               ?>
                                                           </select>
                                                     </div>
                                                      </div>

                        
                                            <!-- Rent + Move-in Date -->
                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>Rent Amount</label>
                                                    <input  type="number"  id="movein_rent_amount"  class="form-control"  readonly>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>Move-In Date</label>
                                                    <input type="date" id="movein_date" class="form-control" required>
                                                </div>
                                            </div>
                        
                                            <!-- Deposit + First Rent + Payment Method -->
                                            <div class="form-row">
                                                <div class="form-group col-md-4">
                                                    <label>Deposit Amount Paid</label>
                                                    <input type="number" id="deposit_paid" class="form-control" value="0" min="0">
                                                </div>
                        
                                                <div class="form-group col-md-4">
                                                   <div>
                                                          <label>First Rent Paid?</label><br>
                                                          <label>
                                                            <input type="radio" name="first_rent_paid" value="yes" required> Yes
                                                          </label>
                                                          <label style="margin-left:15px;">
                                                            <input type="radio" name="first_rent_paid" value="no"> No
                                                          </label>
                                                        </div>
                                                        </div>
                        
                                                <div class="form-group col-md-4">
                                                    <label>Payment Method</label>
                                                    <select id="payment_method" class="form-control" required>
                                                        <option value="cash">Cash</option>
                                                        <option value="mpesa">M-Pesa</option>
                                                    </select>
                                                </div>
                                            </div>
                        
                                            <button type="submit" class="btn btn-info">Move In Tenant</button>
                                        </form>
                        
                                        <div id="moveInMessage" class="mt-3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
            <!-- MOVE OUT -->
            <div class="col-xl-6 col-xxl-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Tenant Move-Out</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form id="moveOutForm">
                                <div class="form-group">
                                    <label style="color: #333333;">Select Tenant / House</label>
                                    <select id="select_tenancy" class="form-control" required>
                                        <option value="">Select</option>
                                        <?php
                                        $tenancies = $conn->query("
                                            SELECT t.id, u.name, h.house_number
                                            FROM tenancies t
                                            JOIN tenants te ON t.tenant_id=te.id
                                            JOIN users u ON te.user_id=u.id
                                            JOIN houses h ON t.house_id=h.id
                                            WHERE t.status='active'
                                        ");
                                        while($ten = $tenancies->fetch_assoc()){
                                            echo "<option value='{$ten['id']}'>{$ten['name']} - {$ten['house_number']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label style="color: #333333;">Move-Out Date</label>
                                    <input type="date" id="move_out_date" class="form-control" required>
                                </div>

                                <button type="submit" class="btn btn-danger">Move Out</button>
                            </form>

                            <div id="moveOutMessage" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.getElementById("addTenantForm").addEventListener("submit", function(e){
    e.preventDefault();

    fetch("../api/tenants/add.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            name: tenant_name.value,
            email: tenant_email.value,
            phone: tenant_phone.value,
            id_number: tenant_id_number.value,
            username: tenant_username.value,
            password: tenant_password.value,
            next_of_kin_name: tenant_next_of_kin_name.value,
            next_of_kin_phone: tenant_next_of_kin_phone.value,
            address: tenant_address.value
        })
    })
    .then(res => res.text()) // 👈 TEMP
    .then(data => {
        console.log("RAW RESPONSE:", data);
        document.getElementById("tenantMessage").innerHTML = data;
    })
    .catch(err => console.error(err));
});
</script>

<script>
document.getElementById("movein_select_house").addEventListener("change", function () {
    const houseId = this.value;
    const rentInput = document.getElementById("movein_rent_amount");

    if (!houseId) {
        rentInput.value = "";
        rentInput.readOnly = false;
        return;
    }

    fetch(`../api/houses/get_house_rent.php?house_id=${houseId}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                rentInput.value = data.rent_amount;
                rentInput.readOnly = true;
            } else {
                rentInput.value = "";
                rentInput.readOnly = false;
                alert(data.message);
            }
        })
        .catch(err => console.error(err));
});
</script>

<?php
require("foott.php");
?>
