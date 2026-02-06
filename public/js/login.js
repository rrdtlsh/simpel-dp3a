document.addEventListener("DOMContentLoaded", function () {
    const btnLogin = document.querySelector(".btn-login");

    btnLogin.addEventListener("click", function () {
        const username = document.querySelector('input[type="text"]').value;
        const password = document.querySelector('input[type="password"]').value;

        if (username === "" || password === "") {
            alert("Username dan Password wajib diisi!");
            return;
        }

        // SEMENTARA (nanti ganti pakai PHP + session)
        alert("Login berhasil (simulasi)");
    });
});
