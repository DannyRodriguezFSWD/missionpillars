@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('help') !!}
@endsection
@section('content')

<style>
        .video-card {
            margin-bottom: 20px;
        }
        .video-card .card-header {
            font-weight: bold;
            text-align: center;
        }
        .video-card .card-body {
            text-align: center;
            position: relative;
        }
        .video-card .card-body iframe {
            /* original */
            /* width: 560px !important;
            height: 315px !important; */
            
            /* resize to accommodate bootstrap column widts */
            width: 400px !important;
            height: 225px !important;
        }
        </style>
<div class="card">
    <div class="card-header">
        <h1>
            Got Questions, We've Got Answers
        </h1>
    </div>
    <div class="card-body">
        <p>A lot of answers to common questions can be found in our Knowledge Base and Training Courses.</p>
        <div class='spacer'></div>
        <div class='spacer'></div>

        <div class='row'>
            <div class='col'>
                <div class="card">
                    <div class="card-header bg-white bold">
                        <i class="fa fa-question"></i> Help Articles
                    </div>
                    <div class="card-body">
                        <blockquote class="blockquote mb-0">
                            <a class='btn btn-success' target='_blank' href="https://support.continuetogive.com/">
                                <i class="fa fa-info"></i> Knowledge Base </a>
                        </blockquote>
                    </div>
                </div>
            </div>

            <div class='col'>
                <div class="card">
                    <div class="card-header bg-white bold">
                        <i class="fa fa-university"></i> Training Course Videos
                    </div>
                    <div class="card-body">
                        <blockquote class="blockquote mb-0">
                            <a class='btn btn-success' target='_blank' href="https://nonprofitgurus.club/course/communicating-with-donors/"><i class="fa fa-graduation-cap"></i>&nbsp;&nbsp;View Training</a>
                        </blockquote>
                    </div>
                </div>
            </div>
        </div>

        <p>
            If you are not able to find answers there, please click the following button and use the form to contact our support team.
        </p>
        <p>
            <em>
                Please be specific in your request so our team can find a solution.<br/>
                If you would like a call back, please include your phone number.
            </em>
        </p>
        <div class="input-group-btn">
            <a href="https://support.continuetogive.com/Tickets/Create" class="btn btn-primary" target="_blank">
                <i class="fa fa-paper-plane"></i>
                Contact Support
            </a>
        </div>

        <p>
            Or email us at <a href="mailto:customerservice@continuetogive.com">customerservice@continuetogive.com</a>
        </p>

        <p>
            Common Questions
        </p>

        <ol>
            <li>
                <a href="https://support.continuetogive.com/KnowledgeBase/Details/?id=56" target="_blank">Selling Tickets For Events</a>
            </li>
            <li>
                <a href="https://support.continuetogive.com/KnowledgeBase/Details/?id=53" target="_blank">Customizable Forms</a>
            </li>
            <li>
                <a href="https://support.continuetogive.com/KnowledgeBase/Details/?id=64" target="_blank">Contribution Statements</a>
            </li>
            <li>
                <a href="https://support.continuetogive.com/KnowledgeBase/Details/?id=55" target="_blank">Emails / Print</a>
            </li>
            <li>
                <a href="https://support.continuetogive.com/KnowledgeBase/Details/?id=67" target="_blank">SMS / Text Messages</a>
            </li>
            <li>
                <a href="https://support.continuetogive.com/KnowledgeBase/Details/?id=54" target="_blank">Group Signup</a>
            </li>
            <li>
                <a href="https://support.continuetogive.com/KnowledgeBase/Details/?id=52 " target="_blank">Child Check In</a>
            </li>
        </ol>

        <h1>
            Help Videos
        </h1>

        <div class="row">
            <div class="col-xl-6">
                <div class="video-card card">
                    <div class="card-header">
                        Management System Overview
                    </div>
                    <div class="card-body">
                        <iframe src="https://www.youtube.com/embed/GbN_LIGTbjw" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        <p>
                            Watch this video to learn all the ins and outs of the Mission Pillars Management System
                        </p>
                        <p class="text-left">
                            For specific features guides you can navigate to certain parts of the video.
                        </p>
                        <ul class="text-left">
                            <li>Custom Dashboard [1:56]</li>
                            <li>Contacts [3:10]</li>
                            <li>Tags, Purposes, and Lists [6:15]</li>
                            <li>Contributions [9:15]</li>
                            <li>Events / Custom Forms [11:00]</li>
                            <li>Group Signup [21:43]</li>
                            <li>Pledges [25:40]</li>
                            <li>Lists/Filters [29:30]</li>
                            <li>Mass Email / Print [31:27]</li>
                            <li>Mass SMS/Text [35:37]</li>
                            <li>Individual SMS and Email [37:00]</li>
                            <li>Users/Roles [39:42]</li>
                            <li>Child Check In [40:55]</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="video-card card">
                    <div class="card-header">
                        Fund Accounting Overview
                    </div>
                    <div class="card-body">
                        <iframe src="https://www.youtube.com/embed/LO3jHrHP4qw" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        <p>
                            Watch this video to learn all the ins and outs of the Mission Pillars Fund Accounting System
                        </p>
                        <p class="text-left">
                            For specific features guides you can navigate to certain parts of the video.
                        </p>
                        <ul class="text-left">
                            <li>Fund accounting 101 [1:00]</li>
                            <li>Chart Of Accounts Setup [5:16]</li>
                            <li>Bank Integration [9:55]</li>
                            <li>Auto Batch Mapping Setup [12:14]</li>
                            <li>Map Bank Deposit to Specific Contributions [14:25]</li>
                            <li>Reconcile multiple transactions (Bank and Credit Card Payment) [16:20]</li>
                            <li>Mapping a Credit card payment to multiple funds [17:20]</li>
                            <li>Registrars [19:00]</li>
                            <li>Journal Entries [19:27]</li>
                            <li>Fund Transfers [20:05]</li>
                            <li>Starting Balances [21:35]</li>
                            <li>Reports (Balance Sheet, P/L, etc) [22:08]</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer">&nbsp;</div>
</div>

@endsection
