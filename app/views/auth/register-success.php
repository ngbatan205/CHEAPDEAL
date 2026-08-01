<section class="auth-section py-5">
    <div class="container">
        <div class="row justify-content-center">

            <div class="col-lg-7">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body p-4 p-lg-5">

                        <div class="success-icon mx-auto mb-4">
                            <i class="bi bi-check-lg"></i>
                        </div>

                        <p class="eyebrow">
                            Registration complete
                        </p>

                        <h1 class="h2 fw-bold mb-3">
                            Welcome,
                            <?= e($confirmation['name']) ?>!
                        </h1>

                        <p class="lead text-secondary">
                            Your CheapDeals customer account has been
                            created successfully.
                        </p>

                        <div class="alert alert-info text-start mt-4" role="status">
                            <i class="bi bi-envelope-check" aria-hidden="true"></i>
                            <span>
                                <strong>Confirmation prepared for <?= e($confirmation['email']) ?>.</strong><br>
                                External email delivery is not enabled in this demonstration, so no message has been sent.
                            </span>
                        </div>

                        <a
                            class="btn btn-primary btn-lg mt-3"
                            href="<?= url('/login') ?>"
                        >
                            Continue to login
                        </a>

                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
