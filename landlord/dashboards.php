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
                                      <!-- MOVE IN (HTML snippet) -->
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
            <label>Rent Amount (monthly)</label>
            <input type="number" id="movein_rent_amount" class="form-control" readonly>
        </div>
        <div class="form-group col-md-6">
            <label>Move-In Date</label>
            <input type="date" id="movein_date" class="form-control" required>
        </div>
    </div>

    <!-- PRORATE DISPLAY (readonly) -->
    <div class="form-row">
        <div class="form-group col-md-6">
            <label>Prorated Amount (readonly)</label>
            <input type="number" step="0.01" id="prorate_amount" class="form-control" readonly>
        </div>

        <div class="form-group col-md-6">
            <label>Amount Paid (by tenant now)</label>
            <input type="number" step="0.01" id="amount_paid" class="form-control" value="0" min="0">
        </div>
    </div>

   

    <!-- Deposit + Payment Method -->
    <div class="form-row">
        <div class="form-group col-md-4">
            <label>Deposit Amount Paid</label>
            <input type="number" id="deposit_paid" class="form-control" value="0" min="0">
        </div>

        <div class="form-group col-md-4">
            <label>Payment Method</label>
            <select id="payment_method" class="form-control" required>
                <option value="cash">Cash</option>
                <option value="mpesa">M-Pesa</option>
            </select>
        </div>

        <div class="form-group col-md-4">
            <label>Rent Balance Preview</label>
            <div id="rentBalancePreview" class="form-control" style="height:auto;background:#f8f9fa;"></div>
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
// --- helper: fetch rent for house (keeps your existing behavior) ---
document.getElementById("movein_select_house").addEventListener("change", function () {
    const houseId = this.value;
    const rentInput = document.getElementById("movein_rent_amount");
    const prorateInput = document.getElementById("prorate_amount");
    const amountPaidInput = document.getElementById("amount_paid");

    if (!houseId) {
        rentInput.value = "";
        rentInput.readOnly = false;
        prorateInput.value = "";
        updateBalancePreview();
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
        .catch(err => console.error(err))
        .finally(() => {
            // update prorate whenever house changes and date is present
            calculateProrate();
        });
});

// Calculate prorated amount and populate readonly field + preview
function calculateProrate() {
    const houseId = document.getElementById("movein_select_house").value;
    const moveDate = document.getElementById("movein_date").value;
    const prorateInput = document.getElementById("prorate_amount");
    const preview = document.getElementById("rentBalancePreview");

    if (!houseId || !moveDate) {
        prorateInput.value = "";
        preview.innerHTML = "";
        return;
    }

    fetch(`../api/tenancies/calc_prorate.php?house_id=${houseId}&move_in_date=${encodeURIComponent(moveDate)}`)
        .then(res => res.json())
        .then(data => {
            if (data.status !== "success") {
                prorateInput.value = "";
                preview.innerHTML = `<span style="color:red">${data.message || 'Calculation error'}</span>`;
                return;
            }

            // set prorated amount
            const amt = (typeof data.amount !== 'undefined') ? parseFloat(data.amount) : parseFloat(data.full_rent || data.rent || 0);
            prorateInput.value = amt.toFixed(2);

            // update preview
            if (data.type === "full") {
                preview.innerHTML = `<strong>Full month charge:</strong> KES ${amt.toFixed(2)}`;
                preview.style.color = "#155724"; // green
            } else {
                preview.innerHTML = `
                    <strong>Prorated</strong><br>
                    Full Rent: KES ${data.full_rent}<br>
                    Days in Period: ${data.period_days}<br>
                    Days owed: ${data.days_owed}<br>
                    <hr>
                    <strong>Amount to charge now: KES ${amt.toFixed(2)}</strong>
                `;
                preview.style.color = "#856404"; // amber
            }

            // also update balance preview using any amount already typed
            updateBalancePreview();
        })
        .catch(err => {
            console.error(err);
        });
}

function updateBalancePreview() {
    const prorateVal = parseFloat(document.getElementById("prorate_amount").value || 0);
    const paidVal = parseFloat(document.getElementById("amount_paid").value || 0);
    const preview = document.getElementById("rentBalancePreview");

    if (isNaN(prorateVal)) { preview.innerHTML = ''; return; }

    const remaining = +(prorateVal - paidVal).toFixed(2); // positive = tenant owes; negative = landlord owes (credit)

    if (remaining > 0) {
        preview.innerHTML = `<strong>Tenant still owes:</strong> KES ${remaining.toFixed(2)}`;
        preview.style.background = "#fff3cd";
    } else if (remaining === 0) {
        preview.innerHTML = `<strong>No balance:</strong> KES 0.00 — settled for this period`;
        preview.style.background = "#d4edda";
    } else {
        preview.innerHTML = `<strong>Landlord owes tenant (credit):</strong> KES ${Math.abs(remaining).toFixed(2)}`;
        preview.style.background = "#cce5ff";
    }
}

// wire up listeners
document.getElementById("movein_date").addEventListener("change", calculateProrate);
document.getElementById("amount_paid").addEventListener("input", updateBalancePreview);
document.getElementById("prorate_amount").addEventListener("change", updateBalancePreview);

// MOVE IN form submit
document.getElementById("moveInForm").addEventListener("submit", function(e){
    e.preventDefault();

    const houseId = document.getElementById("movein_select_house").value;
    const tenantId = document.getElementById("movein_select_tenant").value;
    const moveInDate = document.getElementById("movein_date").value;
    const depositPaid = parseFloat(document.getElementById("deposit_paid").value || 0);
    const amountPaid = parseFloat(document.getElementById("amount_paid").value || 0);
    const paymentMethod = document.getElementById("payment_method").value;
    const prorateAmount = parseFloat(document.getElementById("prorate_amount").value || 0);

    if (!houseId || !tenantId || !moveInDate) {
        alert("Please select house, tenant and move-in date.");
        return;
    }

    // Optional extra check: ensure amountPaid is not negative
    if (amountPaid < 0 || depositPaid < 0) {
        alert("Amounts cannot be negative");
        return;
    }

    fetch("../api/tenancies/move_in.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            house_id: houseId,
            tenant_id: tenantId,
            move_in_date: moveInDate,
            deposit_paid: depositPaid,
            amount_paid: amountPaid,
            prorate_amount: prorateAmount,
            payment_method: paymentMethod
        })
    })
    .then(res => res.json())
    .then(data => {
        const msgDiv = document.getElementById("moveInMessage");
        if (data.status === "success") {
            msgDiv.innerHTML = `<span style="color:green">${data.message}</span>
                                <div>Prorated charged: KES ${data.prorate_amount}</div>
                                <div>Rent balance stored: KES ${data.rent_balance}</div>`;
            document.getElementById("moveInForm").reset();
            document.getElementById("prorate_amount").value = "";
            document.getElementById("rentBalancePreview").innerHTML = "";
            // optionally refresh house/tenant selects (you can add code to reload lists)
        } else {
            msgDiv.innerHTML = `<span style="color:red">${data.message}</span>`;
        }
    })
    .catch(err => {
        console.error("AJAX error:", err);
    });
});
</script>

<?php
require("foott.php");
?>
