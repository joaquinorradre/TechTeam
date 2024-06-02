<!DOCTYPE html>
<html>
<head>
    <title>Formulario de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Formulario de Usuario</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="/analytics/unfollow" method="POST">
        @method('DELETE')

        <div class="mb-3">
            <label for="userId" class="form-label">User ID</label>
            <input type="number" class="form-control" id="userId" name="userId" required>
            @error('userId')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="streamerId" class="form-label">Streamer ID</label>
            <input type="text" class="form-control" id="streamerId" name="streamerId" required>
            @error('streamerId')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-danger">Unfollow</button>
    </form>
</div>
</body>
</html>
