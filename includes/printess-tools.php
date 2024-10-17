<?php

if ( class_exists( 'PrintessTools', false ) ) return;

class PrintessTools
{
  public static function encrypt(string $input) {
    $iv_length = openssl_cipher_iv_length( "aes-256-cbc" );
    $iv = openssl_random_pseudo_bytes( $iv_length );
    $a = openssl_encrypt( $input, "aes-256-cbc", get_option( 'printess_service_token', '' ), OPENSSL_RAW_DATA ,$iv );    
    $b = hash_hmac( 'sha3-512', $a, get_option( 'printess_shop_token', '' ), TRUE );

    return base64_encode( $iv . $a . $b );
  }

  public static function decrypt(string $encrypted) : string | bool {
    $mix = base64_decode($encrypted);
    $iv_length = openssl_cipher_iv_length("aes-256-cbc");
    
    $iv = substr($mix,0, $iv_length);
    $a = substr( $mix, $iv_length, 64 );
    $b = substr( $mix, $iv_length + 64);

    $data = openssl_decrypt( $a, "aes-256-cbc", get_option( 'printess_service_token', '' ), OPENSSL_RAW_DATA, $iv);
    $b_new = hash_hmac('sha3-512', $a, get_option( 'printess_shop_token', '' ), TRUE);
    
    if( hash_equals( $b, $b_new )) {
      return $data;
    }

    return false;
  }

  public static function replace_last_occurance($search, $replace, $subject)
  {
    $pos = strrpos($subject, $search);

    if($pos !== false)
    {
      $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }

    return $subject;
  }
}







?>