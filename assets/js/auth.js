document.getElementById("loginForm").addEventListener("submit", async function(e) {
    e.preventDefault();
    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;
  
    const res = await fetch("api/auth/login.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ username, password }),
    });
  
    const data = await res.json();
    if (data.status === "success") {
      window.location.href = data.role === "admin" ? "pages/admin_panel.php?action=overview" : "pages/dashboard.php";
    } else {
      const errorDiv = document.getElementById("error-message");
      errorDiv.textContent = "Login failed: " + data.message;
      errorDiv.style.display = "block";
    }
  });