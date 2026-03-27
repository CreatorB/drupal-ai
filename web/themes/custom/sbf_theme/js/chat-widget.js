(function (Drupal, once) {
  Drupal.behaviors.aiChatWidget = {
    attach(context) {
      once('ai-chat-widget', '[data-ai-chat]', context).forEach((widget) => {
        const toggleButtons = widget.querySelectorAll('[data-ai-chat-toggle]');
        const suggestionButtons = widget.querySelectorAll('[data-ai-chat-suggestion]');
        const messages = widget.querySelector('[data-ai-chat-messages]');
        const form = widget.querySelector('[data-ai-chat-form]');
        const input = widget.querySelector('[data-ai-chat-input]');

        if (!messages || !form || !input) {
          return;
        }

        toggleButtons.forEach((button) => {
          button.addEventListener('click', () => {
            widget.classList.toggle('is-open');
            if (widget.classList.contains('is-open')) {
              input.focus();
            }
          });
        });

        suggestionButtons.forEach((button) => {
          button.addEventListener('click', () => {
            input.value = button.getAttribute('data-ai-chat-suggestion') || '';
            input.focus();
          });
        });

        form.addEventListener('submit', async (event) => {
          event.preventDefault();
          await sendQuery();
        });

        async function sendQuery() {
          const query = input.value.trim();
          if (!query) {
            return;
          }

          addMessage(query, 'user');
          input.value = '';

          const loadingMessage = addMessage(Drupal.t('Searching...'), 'loading');

          try {
            const response = await fetch('/ai-search-api?q=' + encodeURIComponent(query), {
              headers: {
                Accept: 'application/json',
              },
            });

            if (!response.ok) {
              throw new Error('Chat request failed with status ' + response.status);
            }

            const data = await response.json();
            loadingMessage.remove();

            let html = '';
            if (data.ai_status) {
              const modeLabel = data.ai_status === 'ok'
                ? Drupal.t('AI mode')
                : Drupal.t('Fallback mode');
              html += '<div class="chat-response-meta">' + escapeHtml(modeLabel) + '</div>';
            }

            if (data.conversation) {
              html += '<p>' + escapeHtml(data.conversation) + '</p>';
            }

            if (Array.isArray(data.results) && data.results.length > 0) {
              data.results.slice(0, 5).forEach((item) => {
                const isExternal = Boolean(item.is_external);
                const target = isExternal ? ' target="_blank" rel="noopener noreferrer"' : '';
                const source = item.source ? escapeHtml(item.source) : 'local';
                const sourceLabel = item.source_label
                  ? ' <span class="source-tag source-tag--' + source + '">' + escapeHtml(item.source_label) + '</span>'
                  : '';

                html += '<a class="chat-result-link" href="' + escapeHtml(item.url) + '"' + target + '>'
                  + escapeHtml(item.title) + sourceLabel + '</a>';
              });
            }
            else {
              html += '<p>' + escapeHtml(Drupal.t('Try asking about finance, technology, or search for specific topics like "kubernetes" or "green bonds".')) + '</p>';
            }

            addMessageHtml(html, 'bot');
          }
          catch (error) {
            loadingMessage.remove();
            addMessage(Drupal.t('Sorry, something went wrong. Please try again.'), 'bot');
          }

          messages.scrollTop = messages.scrollHeight;
        }

        function addMessage(text, type) {
          const wrapper = document.createElement('div');
          wrapper.className = 'chat-message chat-message--' + type;
          wrapper.innerHTML = '<p>' + escapeHtml(text) + '</p>';
          messages.appendChild(wrapper);
          messages.scrollTop = messages.scrollHeight;
          return wrapper;
        }

        function addMessageHtml(html, type) {
          const wrapper = document.createElement('div');
          wrapper.className = 'chat-message chat-message--' + type;
          wrapper.innerHTML = html;
          messages.appendChild(wrapper);
          messages.scrollTop = messages.scrollHeight;
          return wrapper;
        }

        function escapeHtml(value) {
          const helper = document.createElement('div');
          helper.textContent = value;
          return helper.innerHTML;
        }
      });
    },
  };
})(Drupal, once);
