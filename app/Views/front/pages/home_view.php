<?php
$this->extend('front/layouts/front_base');
$this->section('title');
echo $title;
$this->endSection();



$this->section('content')
?>

<?php
if (session()->getFlashdata('error') != null) {
    echo "<p class='alert alert-danger'>" . session()->getFlashdata('error') . "</p>";
} elseif (session()->getFlashdata('success')) {
    echo "<p class='alert alert-success'>" . session()->getFlashdata('success') . "</p>";
}
?>
<h6>Coming soon</h6>
<form action=""></form>

<?php $this->endSection() ?>