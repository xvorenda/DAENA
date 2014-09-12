<?php

/* Get things started */
include 'assets/header.php';
include 'assets/navigation.php';

echo "
    <div id='about'>
        <h1>About DAENA</h1>
        <p>DAENA (Data Aggregation and Emergency Notifications for Appliances) was created out of a combination of necessity and frugality - we neeeded a freezer monitoring solution, but didn't want to pay a lot of money for it. We found <a href='http://www.networkedrobotics.com/'>Networked Robotics</a> and their <a href='http://www.networkedrobotics.com/checkoutNTMS4.htm'>NTMS</a> devices, and those were cheap and effective, but the software provided only runs on Windows XP. We wanted a solution that would work in a hybrid environment, and ideally on or from any kind of device. So DAENA was born. DAENA's server components run on Python, MySQL, PHP, and Apache, and the graphs can be viewed from any browser that uses Javascript (except some versions of IE apparently). </p>
        <p>Github repo at <a href='https://github.com/xvorenda/daena' target='_blank'>https://github.com/xvorenda/daena</a></p>
        <h1>Contact Us</h1>
        <p>If you have questions, comments, or suggestions, please email us at <a href='mailto:xvorenda@vcu.edu'>xvorenda@vcu.edu</a> and/or  <a href='mailto:voegtlylj@vcu.edu'>voegtlylj@vcu.edu</a>.</p>
    </div>";      

/* Wrap things up */
include "assets/footer.php";
?>