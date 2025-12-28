<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
</head>
<body>
    <h1>Hello, {{ auth('api')->user()->name }}</h1>
    <form action="/logout" method="POST">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>
</html>