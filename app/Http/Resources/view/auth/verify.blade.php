<form method="POST" action="{{ route('verification.verify') }}">
    @csrf
    <label for="email">Correo electrónico:</label>
    <input type="email" name="email" required>

    <label for="verification_code">Código de verificación:</label>
    <input type="text" name="verification_code" required>

    <button type="submit">Verificar</button>
</form>
