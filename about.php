<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <link rel="stylesheet" href="assets/css/style.css" />
</head>

<body>
    <?php include "includes/header.php"; ?>
    <main class="about-us-page">
        <section class="about-header">
            <h2 class="heading">About TechVolt</h2>
            <h4 class="sub-heading">Powering Ideas. Building Projects.</h4>
            <p class="description-about">TechVolt is your online destination for quality electronic components, helping students, hobbyists, engineers, and electronics enthusiasts bring their ideas to life.</p>
        </section>
        <section class="who-we-are-container">
            <div class="who-we-are">
                <div class="image-slider">
                    <div class="slides ">
                        <img src="assets/images/Slider/slider-for-about-1.png" class="slide active ">
                        <img src="assets/images/Slider/slider-for-about-2.png" class="slide ">
                        <img src="assets/images/Slider/slider-for-about-3.png" class="slide">
                    </div>
                </div>
                <div class="who-content">
                    <h1 class="heading-con-who">WHO WE ARE?</h1>
                    <h2 class="sub-heading-con-who">Building Ideas with the Right Components</h2>
                    <p class="who-desc">
                        TechVolt is an e-commerce platform for electronic components, created to make it easier to find and purchase the components
                        needed for electronics projects.
                    </p>
                    <p class="who-desc">
                        We offer a wide range of products, from microcontrollers and sensors to displays, ICs, motors, power modules, and other
                        essential components.
                    </p>
                    <a class="explore-product" href="products.php">Explore Products →</a>
                </div>
            </div>
        </section>
        <section class="what-we-offer-container" >
            <h1 style="text-align: center;">&bull; WHAT WE OFFER &bull;</h1>
            <div class="what-we-offer">
                <div class="cards">
                    <div class="icon-circle">
                        <img src="assets/images/Icons/puzzle.png" alt="Gauranty of all products">
                    </div>
                    <h4 class="offer-heading">Wide Range of Components</h4>
                    <p class="offer-desc">Explore components for different electronics projects.</p>
                </div>
                <div class="cards">
                    <div class="icon-circle">
                        <img src="assets/images/Icons/guarantee.png" alt="Gauranty of all products">
                    </div>
                    <h4 class="offer-heading">Quality Products</h4>
                    <p class="offer-desc">Reliable components for your development and learning.</p>
                </div>
                <div class="cards">
                    <div class="icon-circle">
                        <img src="assets/images/Icons/wrench.png" alt="Gauranty of all products">
                    </div>
                    <h4 class="offer-heading">For Every Project</h4>
                    <p class="offer-desc">Find components for learning, prototyping and building.</p>
                </div>
            </div>
        </section>
        <section class="our-mission">
            <h2>&bull; OUR MISSION &bull;</h2>
            <p class="long-desc-for-our-mission">"Our mission is to make quality electronic components easy to discover, accessible, and convenient to purchase for students, hobbyists, engineers, and electronics enthusiasts."</p>
            <p class="short-desc-for-our-mission">Helping you turn ideas into real-world projects with the right components.</p>
        </section>
        <section class="maker-container">
            <h2>BUILT FOR <span>STUDENTS</span>, DESIGNED FOR <span>MAKERS</span>.</h2>
            <p>TechVolt supports students,hobbyists, engineers, and electronics enthusiasts by providing the right components for learning, prototyping, and innovation.</p>
        </section>
        <section class="cta-container">
            <h2>&bull; READY TO BUILD SOMETHING?</h2>
            <p>Explore our wide range of electronic components and find everything you need for your next project.</p>
            <button class="cta-btn" onclick="window.location.href='products.php'">BROWSE PRODUCTS &#10132;</button>
        </section>
    </main>
    <?php include "includes/footer.php" ?>
    <script src="assets/Js/slider.js"></script>
    <script src="assets/Js/main.js"></script>
</body>

</html>