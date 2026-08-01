<?php if (!$customer): ?>
    <section class="section narrow">
        <h1>Customer not found</h1>
        <p>The requested customer account does not exist.</p>
        <a class="btn primary" href="<?= url('/crm/enquiries') ?>">Back to messages</a>
    </section>
<?php else: ?>
    <section class="admin-enquiries admin-customer-chat">
        <a class="checkout-back" href="<?= url('/crm/enquiries') ?>">
            <i class="bi bi-arrow-left"></i> Back to customer messages
        </a>

        <header class="admin-chat-header">
            <span class="admin-chat-avatar">
                <?= e(strtoupper(substr($customer['full_name'], 0, 1))) ?>
            </span>
            <div>
                <p class="eyebrow">Customer conversation</p>
                <h1><?= e($customer['full_name']) ?></h1>
                <p>
                    <?= e($customer['email']) ?>
                    <?php if (!empty($customer['phone'])): ?>
                        &bull; <?= e($customer['phone']) ?>
                    <?php endif; ?>
                </p>
            </div>
            <a class="btn outline small" href="<?= url('/crm/customer?id=' . (int) $customer['id']) ?>">
                <i class="bi bi-person"></i> View customer profile
            </a>
        </header>

        <div class="admin-message-list">
            <?php foreach ($enquiries as $enquiry): ?>
                <article class="admin-message-card">
                    <header>
                        <div>
                            <span class="conversation-category">
                                <?= e($enquiry['category'] ?? 'General') ?>
                            </span>
                            <h2><?= e($enquiry['subject']) ?></h2>
                        </div>
                        <span class="message-status <?= $enquiry['status'] === 'Answered' ? 'is-answered' : '' ?>">
                            <?= e($enquiry['status']) ?>
                        </span>
                    </header>

                    <div class="admin-message-meta">
                        <span>#<?= (int) $enquiry['id'] ?></span>
                        <span><i class="bi bi-box"></i> <?= e($enquiry['package_name'] ?? 'General enquiry') ?></span>
                        <span><i class="bi bi-clock"></i> <?= e(date('d M Y, H:i', strtotime($enquiry['created_at']))) ?></span>
                    </div>

                    <div class="message-thread">
                        <div class="message-bubble">
                            <span class="message-avatar">
                                <?= e(strtoupper(substr($customer['full_name'], 0, 1))) ?>
                            </span>
                            <div>
                                <span><?= e($customer['full_name']) ?></span>
                                <p><?= nl2br(e($enquiry['message'])) ?></p>
                            </div>
                        </div>

                        <?php if (!empty($enquiry['reply'])): ?>
                            <div class="message-bubble admin-bubble">
                                <span class="message-avatar">CD</span>
                                <div>
                                    <span>CheapDeals support</span>
                                    <p><?= nl2br(e($enquiry['reply'])) ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <form class="admin-reply-form" method="post" action="<?= url('/crm/enquiries/reply') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="enquiry_id" value="<?= (int) $enquiry['id'] ?>">
                        <input type="hidden" name="customer_id" value="<?= (int) $customer['id'] ?>">
                        <label>
                            <i class="bi bi-reply"></i>
                            <?= $enquiry['status'] === 'Answered' ? 'Edit your reply' : 'Reply to customer' ?>
                        </label>
                        <textarea
                            name="reply"
                            rows="3"
                            required
                            placeholder="Write a clear and helpful response..."
                        ><?= e($enquiry['reply'] ?? '') ?></textarea>
                        <button class="btn primary small" type="submit">
                            <i class="bi bi-send"></i>
                            <?= $enquiry['status'] === 'Answered' ? 'Update reply' : 'Send reply' ?>
                        </button>
                    </form>
                </article>
            <?php endforeach; ?>

            <?php if (!$enquiries): ?>
                <div class="empty-state">
                    <i class="bi bi-chat-square-text"></i>
                    <h2>No conversations</h2>
                    <p>This customer has not sent any enquiries.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>
