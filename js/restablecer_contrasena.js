// 1. Lógica para mostrar/ocultar contraseñas (los ojitos)
const toggleIcons = document.querySelectorAll('.toggle-password');

toggleIcons.forEach(icon => {
    icon.addEventListener('click', function () {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);

        if (input.type === 'password') {
            input.type = 'text';
            this.src = '../src/iconos/ojo-abierto.png'; // Cambia al icono de abierto si lo tienes
        } else {
            input.type = 'password';
            this.src = '../src/iconos/ojo-cerrado.png'; // Vuelve al cerrado
        }
    });
});

// 2. Validación unificada del formulario
const form = document.getElementById('form-nueva-password');
if (form) {
    form.addEventListener('submit', function (e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const errorDiv = document.getElementById('mensaje-error');

        // Regex: mínimo 8 caracteres, al menos una letra mayúscula y al menos un número
        const regexPassword = /^(?=.*[A-Z])(?=.*\d).{8,}$/;

        // Limpiamos errores previos
        errorDiv.style.display = 'none';
        errorDiv.textContent = '';

        // Comprobación de requisitos de seguridad de la contraseña
        if (!regexPassword.test(password)) {
            e.preventDefault();
            errorDiv.textContent = "La contraseña debe tener al menos 8 caracteres, una letra mayúscula y un número.";
            errorDiv.style.display = 'block';
            return;
        }

        // Comprobación de que ambas contraseñas coinciden
        if (password !== confirmPassword) {
            e.preventDefault();
            errorDiv.textContent = "Las contraseñas no coinciden.";
            errorDiv.style.display = 'block';
            return;
        }
    });
}