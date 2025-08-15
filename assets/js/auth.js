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
      window.location.href = data.role === "admin" ? "admin.html" : "dashboard.html";
    } else {
      alert("Login failed: " + data.message);
    }
  });
  