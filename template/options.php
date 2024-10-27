<form action='options.php' method='post'>

  <h2>Ad Short</h2>
  <p>Please fill out all fields.</p>
  <p>The following ad types can share a slot id if the slot id <strong>is a responsive ad type</strong>:
    <br>
    <em><strong>Banner</strong>, <strong>Square</strong>, <strong>Mobile Banner</strong>, and <strong>Large Mobile Banner</strong></em>
  </p>
  <?php
  settings_fields( $this->option_group );
  do_settings_sections( $this->option_group );
  submit_button();
  ?>

</form>
