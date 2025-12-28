<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
    <h2>Register</h2>
    <form action="/register" method="POST">
        @csrf
        <input type="text" name="name" placeholder="Name" required>
        <input type="email" name="email" placeholder="Email">
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
        <button type="submit">Register</button>
    </form>
</body>
</html>
