// ADD HOUSE
document.getElementById("addHouseForm").addEventListener("submit", function(e) {
    e.preventDefault();

    fetch("../api/houses/add.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            house_number: document.getElementById("house_number").value,
            location: document.getElementById("location").value,
            rent_amount: document.getElementById("rent_amount").value,
            due_day: document.getElementById("due_day").value,
            late_interest_per_day: document.getElementById("late_interest_per_day").value,
                    
        })
    })
    .then(res => res.json())
    .then(data => {
        const msgDiv = document.getElementById("message");
        if (data.status === "success") {
            msgDiv.innerHTML = "<span style='color:green'>" + data.message + "</span>";
            document.getElementById("addHouseForm").reset();
        } else {
            msgDiv.innerHTML = "<span style='color:red'>" + data.message + "</span>";
        }
    })
    .catch(err => console.error("AJAX error:", err));
});



// ADD TENANT
document.getElementById("addTenantForm").addEventListener("submit", function(e){
    e.preventDefault();

    fetch("../api/tenants/add.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            name: document.getElementById("tenant_name").value,
            email: document.getElementById("tenant_email").value,
            phone: document.getElementById("tenant_phone").value,
            id_number: document.getElementById("tenant_id_number").value,
            username: document.getElementById("tenant_username").value,
            password: document.getElementById("tenant_password").value,
            next_of_kin_name: document.getElementById("tenant_next_of_kin_name").value,
            next_of_kin_phone: document.getElementById("tenant_next_of_kin_phone").value,
            address: document.getElementById("tenant_address").value
        })
    })
    .then(res => res.json())
    .then(data => {
        const msgDiv = document.getElementById("tenantMessage");
        msgDiv.innerHTML = data.status === "success"
            ? `<span style="color:green">${data.message}</span>`
            : `<span style="color:red">${data.message}</span>`;

        if (data.status === "success") {
            document.getElementById("addTenantForm").reset();
        }
    });
});




// MOVE OUT (unchanged)
document.getElementById("moveOutForm").addEventListener("submit", function(e){
    e.preventDefault();

    fetch("../api/tenancies/move_out.php", {
        method:"POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({
            tenancy_id: document.getElementById("select_tenancy").value,
            move_out_date: document.getElementById("move_out_date").value
        })
    })
    .then(res => res.json())
    .then(data => {
        const msgDiv = document.getElementById("moveOutMessage");
        if(data.status === "success"){
            msgDiv.innerHTML = "<span style='color:green'>"+data.message+"</span>";
            document.getElementById("moveOutForm").reset();
        } else {
            msgDiv.innerHTML = "<span style='color:red'>"+data.message+"</span>";
        }
    })
    .catch(err => console.error("AJAX error:", err));
});
