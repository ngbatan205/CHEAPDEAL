<section class="messages-page">
    <header class="messages-heading">
        <div>
            <p class="eyebrow">Support inbox</p>
            <h1>My messages</h1>
            <p>Follow your enquiries and read replies from the CheapDeals team.</p>
        </div>
        <a class="btn primary" href="<?= url('/enquiry') ?>">
            <i class="bi bi-plus-lg"></i> New enquiry
        </a>
    </header>

    <?php if ($enquiries): ?>
        <div class="conversation-list">
            <?php foreach ($enquiries as $enquiry): ?>
                <article class="conversation-card">
                    <header>
                        <div>
                            <span class="conversation-category"><?= e($enquiry['category'] ?? 'Support') ?></span>
                            <h2><?= e($enquiry['subject']) ?></h2>
                            <p>
                                <?= e($enquiry['package_name'] ?? 'General enquiry') ?>
                                · <?= e(date('d M Y, H:i', strtotime($enquiry['created_at']))) ?>
                            </p>
                        </div>
                        <span class="message-status <?= $enquiry['status'] === 'Answered' ? 'is-answered' : '' ?>">
                            <i class="bi <?= $enquiry['status'] === 'Answered' ? 'bi-check-circle-fill' : 'bi-hourglass-split' ?>"></i>
                            <?= e($enquiry['status']) ?>
                        </span>
                    </header>

                    <div class="message-thread">
                        <div class="message-bubble customer-bubble">
                            <div class="message-avatar">
                                <?= e(strtoupper(substr($customer['full_name'], 0, 1))) ?>
                            </div>
                            <div>
                                <span>You</span>
                                <p><?= nl2br(e($enquiry['message'])) ?></p>
                            </div>
                        </div>

                        <?php if (!empty($enquiry['reply'])): ?>
                            <div class="message-bubble admin-bubble">
                                <div class="message-avatar"><i class="bi bi-headset"></i></div>
                                <div>
                                    <span>CheapDeals support</span>
                                    <p><?= nl2br(e($enquiry['reply'])) ?></p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="waiting-reply">
                                <i class="bi bi-clock"></i>
                                Our support team has received your message and will reply here.
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="messages-empty">
            <i class="bi bi-chat-square-heart"></i>
            <h2>No messages yet</h2>
            <p>Need help choosing a plan or managing an order? Send us an enquiry.</p>
            <a class="btn primary" href="<?= url('/enquiry') ?>">Contact support</a>
        </div>
    <?php endif; ?>
</section>
