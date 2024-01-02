<!DOCTYPE html>
<html>

<head>
    <title>Preparing your file...</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex justify-content-center align-items-center" style="height: 100vh; background-color: #f8f9fa;">
    <div class="text-center">
        <h1 class="mb-4" id="title">Preparing your file...</h1>
        {{-- <div class="spinner-border text-primary mb-3" role="status" id="spinner">
            <span class="sr-only">Loading...</span>
        </div> --}}
        <p id="message">Please wait a few seconds.</p>
        <button id="closeButton"
            style="display: block; margin: auto; padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">Close
            Page</button>

    </div>

    <script>
        setTimeout(function() {
            window.location.href = '/api/' + '{{ $type }}' + '/' + '{{ $model }}' + '/' +
                '{{ $id }}';
        }, 1000);

        // Add event listener to close the page when the button is clicked
        document.getElementById('closeButton').addEventListener('click', function() {
            window.close();
        });
    </script>
</body>

</html>
