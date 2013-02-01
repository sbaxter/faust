( function ( $ ) {
  module( 'ajax' );

  asyncTest( 'reject a request without parameters', function () {

    $.getJSON( 'http://localhost/faust' )
      .fail( function ( xhr, status, msg ) {
        deepEqual( msg, 'Bad Request', 'correctly denied our request' );
        deepEqual( xhr.status, 400, 'correctly denied our request' );
        start();
      } );

  });

  asyncTest( 'reject a request with invalid parameters', function () {

    $.getJSON( 'http://localhost/faust', { invalidParam: 'invalid' } )
      .fail( function ( xhr, status, msg ) {
        deepEqual( msg, 'Bad Request', 'correctly denied our request' );
        deepEqual( xhr.status, 400, 'correctly denied our request' );
        start();
      } );

  });

  asyncTest( 'reject a request with an invalid method', function () {

    $.getJSON( 'http://localhost/faust', { method: 'invalid' } )
      .fail( function ( xhr, status, msg ) {
        deepEqual( xhr.statusText, 'Bad Request', 'correctly denied our request' );
        deepEqual( xhr.status, 400, 'correctly denied our request' );
        start();
      } );

  });

  asyncTest( 'accept a request with a known method', function () {

    $.getJSON( 'http://localhost/faust', { method: 'echo' } )
      .done( function ( data, msg, xhr ) {
        deepEqual( xhr.statusText, 'OK', 'correctly denied our request' );
        deepEqual( xhr.status, 200, 'correctly denied our request' );
        start();
      } );

  });

} )( window.jQuery );
