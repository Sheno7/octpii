<?php
// app/Enums/BookingStatus.php

namespace App\Enums;

class Status
{
    const BOOKINGPENDING = 0;
    const BOOKINGSTARTED = 1;
    const BOOKINGCOMPLETED = 2;
    const BOOKINGCANCELLED = 3;

    const SERVICEVISIBLE = 1;
    const SERVICEINVISIBLE = 0;

    const PACKAGESERVICEPENDING = 0;
    const PACKAGESERVICESTARTED = 1;
    const PACKAGESERVICECOMPLETED = 2;
    const PACKAGESERVICECANCELLED = 3;


    const SERVICELOCATIONINHOUSE = 0;
    const SERVICELOCATIONREMOTE = 1;

    const PAYMENTMETHODCASH = 1;
    const PAYMENTMETHODCARD = 2;

    const PAYMENTPENDING = 0;
    const PAYMENTPROCESSING = 1;
    const PAYMENTCOMPLETED = 2;
    const PAYMENTFAILED = 3;
    const PAYMENTCANCELED = 4;
    const PAYMENTEXPIRED = 5;
    const PAYMENTPARTIAL = 6;

    const BOOKINGSOURCEWEB = 1;
    const BOOKINGSOURCEMOBILE = 2;

    const CUSTOMERREFERRAL = 1;
    const DIRECTREFERRAL = 2;
    const EMAILREFERRAL = 3;
    const SOCIALREFERRAL = 4;
    const WEBSITEREFERRAL = 5;
    const ACTIVE = 1;
    const INACTIVE = 0;

    const ADDREESSCUSTOMER = 1;
    const ADDREESSPROVIDER = 2;
    const PAGINATE = 10;
}
