@extends('layouts.base')

@section('content')
    <section class="section">
        <div class="cards">
            <div class="card-header">
                <div class="d-flex justify-content-between">
                    <h4 class="card-title">Dashboard</h4>
                </div>
            </div>
            <div class="card-body">
                    <br>
                <div class="row">
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div style="border:2px solid #eaebf2;border-radius:15px;" class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-3 d-flex justify-content-start ">
                                        <div class="stats-icon purple mb-2">
                                            <i class="fa-solid fa-cow"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-sm-12 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Manage Cattle</h6>
                                        <h6 class="font-extrabold mb-0">2</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div style="border:2px solid #eaebf2;border-radius:15px;" class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-3 d-flex justify-content-start ">
                                        <div class="stats-icon blue mb-2">
                                            <i class="fa-solid fa-tractor"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Workers</h6>
                                        <h6 class="font-extrabold mb-0">183</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div style="border:2px solid #eaebf2;border-radius:15px;" class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-3 d-flex justify-content-start ">
                                        <div class="stats-icon green mb-2">
                                            <i class="fa-solid fa-toolbox"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Devices</h6>
                                        <h6 class="font-extrabold mb-0">80</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div style="border:2px solid #eaebf2;border-radius:15px;" class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-3 d-flex justify-content-start ">
                                        <div class="stats-icon red mb-2">
                                            <i class="fa-solid fa-rss"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold"> Post</h6>
                                        <h6 class="font-extrabold mb-0">112</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-12 col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4>Profile Visit</h4>
                        </div>
                        <div class="card-body">
                            <div id="chart-profile-visit"></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h4>Visitors Profile</h4>
                        </div>
                        <div class="card-body">
                            <div id="chart-visitors-profile"></div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="card-body">

                <br>
                <div class="row">
                </div>
            </div>
        </div>

    </section>
@endsection


@section('js')
    <script src="{{ url('assets/extensions/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ url('assets/static/js/pages/dashboard.js') }}"></script>
@endsection
