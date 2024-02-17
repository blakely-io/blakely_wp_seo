
if ( document.getElementById("blakely_wp_seo_description") || document.getElementById("blakely_wp_seo_keywords") !== null ) {
  update_output();
}

function update_output(){
  
  // Display the character count for meta description
  const desc_input = document.getElementById("blakely_wp_seo_description");
  const desc_len = document.getElementById("blakely_wp_desc_output");
  
  const desc_dislpay_length = () => {
    desc_len.innerText = desc_input.value.length;
  }
  
  desc_input.addEventListener('input', desc_dislpay_length);
  
  desc_dislpay_length();
  
  // Display the character count for meta keywords
  const keys_input = document.getElementById("blakely_wp_seo_keywords");
  const keys_len = document.getElementById("blakely_wp_keys_output");
  
  const keys_display_length = () => {
    keys_len.innerText = keys_input.value.length;
  }
  
  keys_input.addEventListener('input', keys_display_length);
  
  keys_display_length();
  
}