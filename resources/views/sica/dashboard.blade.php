@extends('layouts.sica.main')
@section('content')

    <!-- Content -->

    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Welcome To </span>SICA SED
        </h4>
        <!-----------FILTER START------------->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="defaultSelect" class="form-label">District</label>
                    <select id="defaultSelect" class="form-select">
                        <option>Default select</option>
                        <option value="1">One</option>
                        <option value="2">Two</option>
                        <option value="3">Three</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="defaultSelect" class="form-label">Tehsils</label>
                    <select id="defaultSelect" class="form-select">
                        <option>Default select</option>
                        <option value="1">One</option>
                        <option value="2">Two</option>
                        <option value="3">Three</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="defaultSelect" class="form-label">Markaz</label>
                    <select id="defaultSelect" class="form-select">
                        <option>Default select</option>
                        <option value="1">One</option>
                        <option value="2">Two</option>
                        <option value="3">Three</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="defaultSelect" class="form-label">School Name</label>
                    <select id="defaultSelect" class="form-select">
                        <option>Default select</option>
                        <option value="1">One</option>
                        <option value="2">Two</option>
                        <option value="3">Three</option>
                    </select>
                </div>
            </div>
        </div>
        <!-----------FILTER END------------->
        <div class="row">
            <div class="col-lg-12 col-md-12 order-1">
                <div class="row">
                    <div class="col-2-half mb-4">
                        <div class="card">
                            <div class="card-body">
                                <span class="d-block mb-1 label-bage">Total Schools</span>
                                <h3 class="card-title text-nowrap mb-2">2,456</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-2-half mb-4">
                        <div class="card">
                            <div class="card-body">
                                <span class="d-block mb-1 label-bage">Total Verified </span>
                                <h3 class="card-title text-nowrap mb-2">2,456</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-2-half mb-4">
                        <div class="card">
                            <div class="card-body">
                                <span class="d-block mb-1 label-bage">Total Un-Verified </span>
                                <h3 class="card-title text-nowrap mb-2">2,456</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-2-half mb-4">
                        <div class="card">
                            <div class="card-body">
                                <span class="d-block mb-1 label-bage">Data Not Submitied</span>
                                <h3 class="card-title text-nowrap mb-2">2,456</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-2-half mb-4">
                        <div class="card">
                            <div class="card-body">
                                <span class="d-block mb-1 label-bage">Pending For Review</span>
                                <h3 class="card-title text-nowrap mb-2">2,456</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Order Statistics -->
            <div class="col-md-6 col-lg-6 col-xl-6 order-0 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-title mb-0">
                            <h4 class="m-0 me-2">Verification Statistics</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="orderStatisticsChart"></div>
                    </div>
                </div>
            </div>
            <!--/ Order Statistics -->

            <!-- Expense Overview -->
            <div class="col-md-6 col-lg-6 order-1 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <div class="card-title mb-0">
                            <h4 class="m-0 me-2">Punjab Map</h4>
                        </div>
                    </div>
                    <div class="card-body px-0">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m28!1m12!1m3!1d27701.41587064758!2d74.28111841899259!3d31.51358092638643!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!4m13!3e0!4m5!1s0x39190382931e3655%3A0x58b2e3b1be62b2f4!2sGovernment%20Pilot%20Secondary%20High%20School%2C%20Wahdat%20Rd%2C%20Asif%20Block%20Allama%20Iqbal%20Town%2C%20Lahore%2C%20Punjab%2C%20Pakistan!3m2!1d31.512222299999998!2d74.3019212!4m5!1s0x39190382931e3655%3A0x58b2e3b1be62b2f4!2sGovernment%20Pilot%20Secondary%20High%20School%2C%20Wahdat%20Rd%2C%20Asif%20Block%20Allama%20Iqbal%20Town%2C%20Lahore%2C%20Punjab%2C%20Pakistan!3m2!1d31.512222299999998!2d74.3019212!5e0!3m2!1sen!2s!4v1712225055502!5m2!1sen!2s"
                            width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
            <!--/ Expense Overview -->
        </div>
    </div>
    <!-- / Content -->
@endsection



