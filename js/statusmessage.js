/**
 * Created by logicp on 6/05/17.
 */
(function($, Drupal, drupalSettings) {
  Drupal.behaviors.status= {
    attach: function (context, settings) {

      Drupal.AjaxCommands.prototype.generatePreview = function(ajax, response, status) {

        if (validateUrl(response.url)) {

          var cleanUrl = response.url.replace(/^http(s?):\/\//i, "");
          // console.log(cleanUrl);
          $.ajax({
            type: 'POST',
            url: '/statusmessage/generate-preview/' + cleanUrl,
            success: function (response) {

              // console.log(response.data);
              if (response.data != null) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(response.data, "text/html");

                var markup = buildPreview(doc);

                var statusBlock = document.getElementById('block-statusblock');
                var oldPreviewIframe = document.querySelector('.statusmessage-preview-iframe');

                if (oldPreviewIframe !== null) {
                  oldPreviewIframe.parentNode.removeChild(oldPreviewIframe);

                }
                previewIframe = document.createElement('iframe');
                previewIframe.classList.add('statusmessage-preview-iframe');
                statusBlock.appendChild(previewIframe);
                previewIframe.contentWindow.document.open();
                previewIframe.contentWindow.document.appendChild(markup)
                previewIframe.contentWindow.document.close();
              }
            }
          });
        }
      };

      function validateUrl(input) {
        return input.match(new RegExp("([a-zA-Z0-9]+://)?([a-zA-Z0-9_]+:[a-zA-Z0-9_]+@)?([a-zA-Z0-9.-]+\\.[A-Za-z]{2,4})(:[0-9]+)?(/.*)?"));
      }

      function buildPreview(doc) {
        var imgs = doc.querySelectorAll('img');
        var metaTags = doc.querySelectorAll('meta');
        var title = doc.querySelector('title');
        var markup;
        var description;
        var previewImage = null;

        imgs.forEach(function (img) {
          if (previewImage === null) {
            var imgClasses = img.classList;

            if (imgClasses.value.toLowerCase().indexOf('logo') || img.alt.toLowerCase().indexOf('logo') || img.title.toLowerCase().indexOf('logo') || img.src.toLowerCase().indexOf('logo')) {
              previewImage = img;
            }
          }
        });

        metaTags.forEach(function (metaTag) {
          if (metaTag.name == 'description') {
            description = metaTag.content;

          }
        });

        console.dir(description);
        console.dir(previewImage.src);
        var outer = document.createElement('div');
        outer.className = 'statusmessage-preview';
        var titlemarkup = document.createElement('h4');
        titlemarkup.innerHTML = title.innerHTML;
        var descmarkup = document.createElement('p');
        descmarkup.innerText = description;
        var imgmarkup = document.createElement('img');
        imgmarkup.src = previewImage.src;


        var wrapper = document.createElement('div');
        wrapper.appendChild(titlemarkup);
        wrapper.appendChild(descmarkup);
        wrapper.appendChild(imgmarkup);

        return wrapper;
      }

    }
  };

})(jQuery, Drupal, drupalSettings);

