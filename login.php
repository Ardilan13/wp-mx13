<!DOCTYPE html>
<html>

<head>
    <title>Formulario de Inicio de Sesión</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title text-center">Iniciar Sesión</h2>
                        <form id="loginForm" method="post">
                            <div class="form-group">
                                <label for="username">Correo:</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Contraseña:</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
                            <p id="error" class="mt-3 text-danger"></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $("#loginForm").submit(function(e) {
                e.preventDefault();

                $.ajax({
                    type: "POST",
                    url: "test.php",
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response === "success") {
                            window.location.href = "index.php/home"; // Página de inicio después del inicio de sesión exitoso
                        } else {
                            $("#error").text("Credenciales inválidas.");
                        }
                        console.log(response);
                    }
                });
            });
        });
    </script>
</body>

</html>