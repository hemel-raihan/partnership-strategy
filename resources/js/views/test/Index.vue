<template>
    <div class="content">
      <div class="container-fluid">
        <div id="map">
            <div id="car" class="car"></div>
        </div>
      </div>
    </div>
  </template>
  <script>

  
  export default {
    data() {
      return {
       
      }
    },
    mounted(){
        document.addEventListener("DOMContentLoaded", function () {
        const car = document.getElementById("car");

        window.addEventListener("keydown", function (event) {
            moveCar(event.key);
            checkLocation();
        });

        function moveCar(key) {
            const currentLeft = parseInt(getComputedStyle(car).left);
            const currentBottom = parseInt(getComputedStyle(car).bottom);
            const step = 50;

            switch (key) {
                case "ArrowRight":
                    car.style.left = currentLeft + step + "px";
                    break;
                case "ArrowLeft":
                    car.style.left = Math.max(0, currentLeft - step) + "px";
                    break;
                case "ArrowUp":
                    car.style.bottom = currentBottom + step + "px";
                    break;
                case "ArrowDown":
                    car.style.bottom = Math.max(0, currentBottom - step) + "px";
                    break;
            }
        }

        function checkLocation() {
            const carPosition = car.getBoundingClientRect();
            const destination = document.getElementById("destination").getBoundingClientRect();

            if (carPosition.left + carPosition.width >= destination.left &&
                carPosition.left <= destination.left + destination.width &&
                carPosition.bottom + carPosition.height >= destination.bottom &&
                carPosition.bottom <= destination.bottom + destination.height) {
                // Trigger an event when the car reaches the destination
                alert("Destination reached! Your event can go here.");
            }
        }
    });
    },
    methods: {
      
    }
  }
  </script>

<style scoped>
body, html {
    margin: 0;
    padding: 0;
    height: 100%;
}

#map {
    position: relative;
    width: 800px;
    height: 400px;
    background-color: #e0e0e0;
}


.car {
    position: absolute;
    width: 80px; /* Adjust width and height based on your car image */
    height: 40px;
    background:  url('http://localhost:8080/pos-billing/assets/images/car.jpg') no-repeat;
    background-size: cover;
    bottom: 10px;
    left: 10px;
    transition: left 0.5s linear, bottom 0.5s linear;
}

.car::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 30px;
    background-color: #ecf0f1;
    border-radius: 5px;
    left: 5px;
}

.car::before {
    content: '';
    position: absolute;
    width: 50px;
    height: 10px;
    background-color: #ecf0f1;
    border-radius: 5px;
    top: 5px;
}
</style>
  