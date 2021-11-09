<!DOCTYPE html>
<html>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    </head>
    <body style="margin:0">
        <div id="debug"> </div>
        <center>
            <div id="weather">
                <table style="width:90%;border-spacing:0" border=0>
                    <tr style="line-height:100%">
                        <td width="1%">
                            <img id='today-icon' style="height: 100px; width: 100px"/>
                        </td>
                        <td>
                            <span id='today-temp' style="line-height:35%; font-size: 100px; font-family:Arial;"></span>
                            <br>
                            <div>
                                Feels like <span id='feels-like'> </span>°
                                <br>
                                Indoor <span id='indoor'></span>°
                            </div>
                        </td>
                        <td>
                            <table>
                                <tr id="day-forecast">
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <table  border=0>
                    <tr id="hour-forecast">
                    </tr>
                </table>
            </div>

            <style>
                #clockContainer {
                    margin: auto;
                    height: 40vw;
                    width: 40vw;
                    background-size: 100%;
                }
                  
                #hour,
                #minute {
                    position: absolute;
                    background: black;
                    border-radius: 10px;
                    -webkit-transform-origin-y: bottom;
                    margin-top: 100px;
                }
                  
                #hour {
                    width: 2.5%;
                    height: 20%;
                    top: 31%;
                    left: 48.85%;
                    opacity: 0.8;
                }
                  
                #minute {
                    width: 1.3%;
                    height: 30%;
                    top: 22%;
                    left: 48.9%;
                    opacity: 0.8;
                }
            </style>

            <div id="clockContainer">
                <div id="hour"></div>
                <div id="minute"></div>
            </div>
        </center>

        <script>
            try {
            
                function get_weather() {
                    var xhttp = new XMLHttpRequest();
                    xhttp.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                            json = JSON.parse(this.responseText);
                            fill_weather(json);
                            update_time(json);
                        }
                    };
                    xhttp.open("GET", "/weather.php", true);
                    xhttp.send();
                }

                function update_time(response) {
                    hr = response.hr;
                    min = response.min;
                    sec = response.sec;
                    tick();
                }

                function fill_weather(response) {
                    const today_temp = document.getElementById('today-temp');
                    const today_icon = document.getElementById('today-icon');
                    today_temp.innerText = response.current.temperature + "°";
                    today_icon.src = response.current.icon;
                    const today_feel = document.getElementById('feels-like');
                    today_feel.innerText = response.current.feelslike;
                    const indoor = document.getElementById('indoor');
                    indoor.innerText = response.current.indoor;

                    const hour_forecast = document.getElementById('hour-forecast');
                    hour_forecast.innerHTML = "";
                    response.hour.forEach(function(hour) {
                        const entry = document.createElement('td');
                        entry.width = "12px";
                        entry.style = "display:inline-grid";
                        const time = document.createElement('span');
                        time.innerText = hour.time;
                        time.style = "font-family:Arial";
                        entry.appendChild(time);
                        entry.appendChild(document.createElement('br'));
                        const icon = document.createElement('img');
                        icon.src = hour.icon;
                        icon.style = "height:12px;width:12px";
                        entry.appendChild(icon);
                        const temp = document.createElement('span');
                        temp.innerText = hour.temperature + "°";
                        temp.style = "font-family:Arial";
                        entry.appendChild(temp);
                        hour_forecast.appendChild(entry);
                    });
                    
                    const day_forecast = document.getElementById('day-forecast');
                    day_forecast.innerHTML = "";
                    response.day.forEach(function(day) {
                        const entry = document.createElement('td');
                        entry.width = "5px";
                        entry.style = "display:inline-grid";
                        const time = document.createElement('span');
                        time.innerText = day.time;
                        time.style = "font-family:Arial";
                        entry.appendChild(time);
                        entry.appendChild(document.createElement('br'));
                        const high = document.createElement('span');
                        high.innerText = day.high + "°";
                        high.style = "font-family:Arial";
                        entry.appendChild(high);
                        const icon = document.createElement('img');
                        icon.src = day.icon;
                        icon.style = "height:5px;width:5px";
                        entry.appendChild(icon);
                        const low = document.createElement('span');
                        low.innerText = day.low + "°";
                        low.style = "font-family:Arial";
                        entry.appendChild(low);
                        day_forecast.appendChild(entry);
                    });
                }
                get_weather();

                setInterval(get_weather, 15*60*1000);

                var hour = document.getElementById('hour');
                var minute = document.getElementById('minute');
                
                d = new Date();
                hr = d.getHours() - 4;
                min = d.getMinutes();
                sec = d.getSeconds();
                    
                function tick() {
                    sec += 30;
                    if (sec > 60) {
                        min += 1;
                        sec -= 60;
                    }
                    if (min > 60) {
                        hr += 1;
                        min -= 60;
                    }
                    if (hr > 24) {
                        hr -= 24;
                    }
                    hr_rotation = 30 * hr + min / 2;
                    min_rotation = 6 * min + sec * 0.1;

                    hour.style['-webkit-transform'] = "rotate(" + hr_rotation + "deg)";
                    minute.style['-webkit-transform'] = "rotate(" + min_rotation + "deg)";
                }
                tick();

                setInterval(tick, 30000);

                function backout() {
                    document.body.style.backgroundColor = "black";
                    document.getElementById('weather').style.display = 'none';
                    setTimeout(function() {
                        document.body.style.backgroundColor = "";
                        document.getElementById('weather').style.display = '';
                    }, 500);
                    setTimeout(backout, 15 * 60 * 1000 + Math.random() * 30 * 60000);
                }

                setTimeout(backout, 15 * 60 * 1000 + Math.random() * 30 * 60000);
            } catch (error) {
                document.getElementById("debug").innerText = error;
            }
        </script>
    </body>
</html>
