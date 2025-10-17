<!DOCTYPE html>
<html lang="en">
<head>
    <title>Fermentation Chamber Dashboard</title>
    <script>
        function fetchData() {
            fetch("get_data.php")
            .then(response => response.json())
            .then(data => {
                document.getElementById("temperature").innerText = data.temp;
                document.getElementById("humidity").innerText = data.hum;
                document.getElementById("pH").innerText = data.ph;
                document.getElementById("density").innerText = data.lc;
            });
        }
        setInterval(fetchData, 5000);
    </script>
</head>
<body>
    <h1>Fermentation Chamber Monitoring</h1>
    <p>Temperature: <span id="temperature"></span> °C</p>
    <p>Humidity: <span id="humidity"></span> %</p>
    <p>pH Level: <span id="pH"></span></p>
    <p>Density: <span id="density"></span></p>
</body>
</html>
