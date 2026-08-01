<?php

class EnquiryController extends Controller
{
    public function create(): void
    {
        $this->view('enquiry/create', [
            'title' => 'Make an enquiry',
            'packages' => (new Package($this->db))->all(),
            'subject' => $this->input('subject'),
        ]);
    }

    public function store(): void
    {
        $customer = $this->currentCustomer();
        $packageInput = $this->input('package_id');
        $packageId = $packageInput === '' ? null : (int) $packageInput;
        $subject = $this->input('subject');
        $message = $this->input('message');
        $errors = [];

        if (mb_strlen($subject) < 3) {
            $errors[] = 'Please enter an enquiry subject of at least 3 characters.';
        }
        if (mb_strlen($message) < 10) {
            $errors[] = 'Please enter a message of at least 10 characters.';
        }
        if ($packageId !== null && !(new Package($this->db))->find($packageId)) {
            $errors[] = 'The selected package is unavailable. Choose another package or submit a general enquiry.';
        }
        if ($errors) {
            $_SESSION['flash'] = implode(' ', $errors);
            redirect('/enquiry');
        }

        (new Enquiry($this->db))->create([
            'user_id' => $customer['id'] ?? null,
            'package_id' => $packageId,
            'subject' => $subject,
            'message' => $message,
        ]);
        $_SESSION['flash'] = 'Thanks, your enquiry has been sent. Our team will reply shortly.';
        redirect($customer ? '/messages' : '/enquiry');
    }
}
