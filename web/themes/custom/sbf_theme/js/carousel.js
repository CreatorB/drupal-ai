(function (Drupal, once) {
  Drupal.behaviors.sbfCarousel = {
    attach(context) {
      once('sbf-carousel', '[data-carousel-track]', context).forEach((track) => {
        const wrap = track.parentElement;
        if (!wrap) {
          return;
        }

        const prev = wrap.querySelector('[data-carousel-prev]');
        const next = wrap.querySelector('[data-carousel-next]');
        const firstCard = track.firstElementChild;
        const scrollAmount = firstCard ? firstCard.getBoundingClientRect().width + 24 : 360;
        let autoTimer;

        function scrollNext() {
          if (track.scrollLeft + track.clientWidth >= track.scrollWidth - 10) {
            track.scrollTo({ left: 0, behavior: 'smooth' });
          }
          else {
            track.scrollBy({ left: scrollAmount, behavior: 'smooth' });
          }
        }

        function startAuto() {
          autoTimer = setInterval(scrollNext, 6000);
        }

        function resetAuto() {
          clearInterval(autoTimer);
          startAuto();
        }

        if (prev) {
          prev.addEventListener('click', () => {
            track.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            resetAuto();
          });
        }

        if (next) {
          next.addEventListener('click', () => {
            scrollNext();
            resetAuto();
          });
        }

        startAuto();
      });
    }
  };

  Drupal.behaviors.sbfHeroCarousel = {
    attach(context) {
      once('sbf-hero-carousel', '[data-hero-carousel]', context).forEach((carousel) => {
        const slides = carousel.querySelectorAll('.hero-carousel__slide');
        const dots = carousel.querySelectorAll('[data-hero-dot]');
        const prevBtn = carousel.querySelector('[data-hero-prev]');
        const nextBtn = carousel.querySelector('[data-hero-next]');
        let current = 0;
        let timer;

        if (!slides.length) {
          return;
        }

        function goTo(index) {
          slides[current].classList.remove('is-active');
          if (dots[current]) {
            dots[current].classList.remove('is-active');
          }
          current = ((index % slides.length) + slides.length) % slides.length;
          slides[current].classList.add('is-active');
          if (dots[current]) {
            dots[current].classList.add('is-active');
          }
        }

        function resetTimer() {
          clearInterval(timer);
          timer = setInterval(() => goTo(current + 1), 5000);
        }

        dots.forEach((dot) => {
          dot.addEventListener('click', () => {
            goTo(parseInt(dot.dataset.heroDot, 10));
            resetTimer();
          });
        });

        if (prevBtn) {
          prevBtn.addEventListener('click', () => {
            goTo(current - 1);
            resetTimer();
          });
        }

        if (nextBtn) {
          nextBtn.addEventListener('click', () => {
            goTo(current + 1);
            resetTimer();
          });
        }

        resetTimer();
      });
    }
  };
})(Drupal, once);
