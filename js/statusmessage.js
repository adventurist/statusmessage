/**
 * Created by logicp on 6/05/17.
 */
(function($, Drupal, drupalSettings) {
  Drupal.behaviors.status= {
    attach: function (context, settings) {

      Drupal.AjaxCommands.prototype.generatePreview = function(ajax, response, status) {

        if (validateUrl(response.url)) {
          console.dir(response);
        }
        var cleanUrl = response.url.replace(/^http(s?):\/\//i, "");
        // console.log(cleanUrl);
        $.ajax({
          type:'POST',
          url:'/statusmessage/generate-preview/' + cleanUrl,
          success: function(response) {

            // console.log(response.data);
            if (response.data != null) {
              var parser = new DOMParser();
              var doc = parser.parseFromString(response.data, "text/html");

              var imgs = doc.querySelectorAll('img')
              var metaTags = doc.querySelectorAll('meta');
              var title = doc.querySelector('title');
              var markup;
              var description;
              var previewImage;

              imgs.forEach(function (img) {
                var imgClasses = img.classList;
                if (imgClasses.value !== null && imgClasses.value.length > 0) {
                  if (imgClasses.value.includes('logo')) {
                    previewImage = img;
                  }
                }
                if (previewImage !== null) {
                }
              });


              metaTags.forEach(function (metaTag) {
                if (metaTag.name == 'description') {
                  description = metaTag.content;

                }
              });

              console.dir(description);
              console.log(previewImage);

              markup = '<div id="statusmessage-preview"><h2> ' + title != null ? title.innerHTML : "No Title</h2>"
                + description != null ? description.innerHTML : "No Description"
                  + '<img src="' + previewImage != null ? previewImage.src + '" />' : 'google images />'
                    + '</div>';

              console.dir(markup);
              console.log(markup);


              var statusBlock = document.getElementById('block-statusblock');
              var previewIframe = document.createElement('iframe');
              previewIframe.classList.add('statusmessage-preview-iframe');
              statusBlock.appendChild(previewIframe);
              previewIframe.contentWindow.document.open();
              previewIframe.contentWindow.document.write(markup);
              previewIframe.contentWindow.document.close();
            }
          }
        });
      };

      function validateUrl(input) {
        return input.match(new RegExp("([a-zA-Z0-9]+://)?([a-zA-Z0-9_]+:[a-zA-Z0-9_]+@)?([a-zA-Z0-9.-]+\\.[A-Za-z]{2,4})(:[0-9]+)?(/.*)?"));
      }

    }
  };

})(jQuery, Drupal, drupalSettings);

