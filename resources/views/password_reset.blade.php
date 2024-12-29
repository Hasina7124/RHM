<!DOCTYPE html>
<html>
<head>
    <title>Réinitialiser le mot de passe</title>
</head>
<body>
    <form method="POST" action="/api/password-reset">
        @csrf
        <input type="hidden" name="token" value="{{ request('token') }}">
        <input type="hidden" name="email" value="raharisonmiharififalianahasina@gmail.com">
        <label>Nouveau mot de passe :</label>
        <input type="password" name="password" required>
        <label>Confirmer le mot de passe :</label>
        <input type="password" name="password_confirmation" required>
        <button type="submit">Réinitialiser le mot de passe</button>
    </form>
</body>
</html>
