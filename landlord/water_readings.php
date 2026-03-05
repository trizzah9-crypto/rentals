<?php
require "headd.php";


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

                 <div class="col-xl-6 col-xxl-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Take water readings</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form id="readings">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                         <select id="house" class="form-control" required>
                                            <option value="">Select House</option>
                                            <?php
                                            $houses = $conn->query("SELECT id, house_number FROM houses WHERE landlord_id=".$_SESSION['user_id']);
                                            while($row = $houses->fetch_assoc()){
                                                echo "<option value='{$row['id']}'>{$row['house_number']}</option>";
                                            }
                                            ?>
                                        </select>
                                     </div>
                                    <div class="form-group col-md-6">
                                        <label style="color: #333333;">Previous Water reading</label>
                                        <input type="text" name="prev" id="prev" class="form-control" readonly>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label style="color: #333333;">Current Tenant</label>
                                        <input type="text" name="tenant" id="tenant" class="form-control" readonly>
                                    </div>
                                    
                                </div>

                                <div class="form-group">
                                    <label style="color: #333333;">Current Water Reading</label>
                                    <input type="number" name="curr" id="curr" class="form-control" required>
                                </div>

                                <button type="submit" class="btn btn-primary">Submit Reading</button>
                            </form>

                            <div id="message" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>

             </div>
        </div>
</div>
<script>
    
    document.getElementById("house").addEventListener("change", function () {
    const houseId = this.value;
    const previous = document.getElementById("prev");
    const tenantId = document.getElementById("tenant");
    

    if (!houseId) {
        previous.value = "";
        previous.readOnly = false;
        return;
    }

    fetch(`../api/water_readings/get_previous.php?house_id=${houseId}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                previous.value = data.previous_water_readings;
                previous.readOnly = true;
            } else {
                previous.value = "";
                previous.readOnly = false;
                alert(data.message);
            }
        })
        .catch(err => console.error(err))

        
    fetch(`../api/water_readings/get_tenant.php?house_id=${houseId}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                tenantId.value = data.username;
                tenantId.readOnly = true;
            } else {
                tenantId.value = "";
                tenantId.readOnly = false;
                alert(data.message);
            }
        })
        .catch(err => console.error(err))


    

});

</script>

<script>
    // SUBMIT  READINGS
document.getElementById("readings").addEventListener("submit", function(e){
    e.preventDefault();
         const houseId = document.getElementById("house") . value;
         const previous = parsefloat(document.getElementById("prev") . value || 0);
         const tenantId = document.getElementById("tenant") . value;
         const current = parseFloat(document.getElementById("curr") . value || 0);

         if  (!houseId || !current || !tenantId){
            alert("House, Tenant and current reading cannot be empty.");
         return; }

         if(current < previous){
            alert("Current reading can not be lower than the previous reading.");
         return; }


          fetch("../api/water_readings/submit_readings.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            house_id: houseId,
            tenant_id: tenantId,
            current_reading: current
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
require "foott.php";
?>