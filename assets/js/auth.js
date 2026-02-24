document.getElementById("loginForm").addEventListener("submit", function(e) {
    e.preventDefault();

    fetch("api/auth/login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            email: email.value,
            password: password.value
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            if (data.role === "landlord") {
                window.location.href = "landlord/dashboard.php";
            } else if (data.role === "tenant") {
                window.location.href = "tenant/dashboard.php";
            }
        } else {
            alert("Login failed");
        }
    });
});
