$(window).on("load", function () {
  $("#night").click(function () {
    var toggle = $("body").hasClass("nightmode");
    $("body").toggleClass("nightmode");

    if (!toggle) {
      hide("greg-body", 0);
      hide("fingers", 0);
      hide("owlbert", 0);

      start("greg-intro-night", 0);
      start("owlbert-night", 1);
      $(".fingers").toggleClass("on", toggle);
      $(".greg-and-owlbert-night").toggleClass("no-events", toggle);

      $("#favicon").attr("href", "/images/favicon-dark.ico");
    } else {
      hide("greg-intro-night", 0);
      hide("owlbert-night", 0);

      start("greg-intro", 0);
      start("owlbert", 1);
      $(".fingers").toggleClass("on", toggle);
      $(".greg-and-owlbert-night").toggleClass("no-events", toggle);
      $("#favicon").attr("href", "/images/favicon.ico");
    }
    return false;
  });

  setTimeout(function () {
    $("body").removeClass("preload");
  }, 2000);

  $("time").timeago();
  /*
  $('.greg').click(() => {
    $('body').toggleClass('show-modal', !$('body').hasClass('show-modal'));
  });
  */

  $(document).keyup(function (e) {
    if (e.keyCode == 27) {
      closeModal();
    }
  });

  $(".modal-bg, .close-modal").click(function () {
    closeModal();
    return false;
  });

  function closeModal() {
    if ($("body").is(".show-modal")) {
      var $el = $(".current");
      if (animationsOff[$el.data("hover")]) {
        animationsOff[$el.data("hover")]();
      }

      $(".modal-bg").removeClass("on");
      $("body").removeClass("show-modal");
      $(".close-modal").hide();
      $(".modal").removeClass(function (index, className) {
        return (className.match(/\bis-\S+/g) || []).join(" ");
      });
      setTimeout(function () {
        $(".greg-img").append($(".overlay-item").children());
        $(".current").removeClass("current");
        $(".modal").removeClass("opening opened");
        $("#modal-content").append($(".modal").children());
      }, 300);

      // New modal stuff
      $("#hover-new").fadeOut();
      $("#hover-new-header").removeClass("on");
      $("body").removeClass("show-modal");
      $("#hover-new").removeClass("opening opened");
    }
  }

  $(".hover-new").click(function () {
    $("html, body").animate({ scrollTop: 0 });

    $("body").addClass("show-modal");
    $("#hover-new > div").hide();
    $("#hover-new > #hover-new-" + $(this).data("hover")).show();
    $("#hover-new").fadeIn();
    $("#hover-new")[0].scrollTop = 0;

    $("#hover-new-header strong").text($(this).data("title"));
    $("#hover-new-header .link").text($(this).data("link-text"));
    $("#hover-new-header .link").attr("href", $(this).data("link"));
    $("#hover-new-header").addClass("on");

    setTimeout(function () {
      $("#hover-new").addClass("opening");
    }, 100);
  });

  $(".hover-new").hover(
    function () {
      var $label = $("." + $(this).data("hover") + "-label");
      animateArrow($label);
    },
    function () {
      var $label = $("." + $(this).data("hover") + "-label");
      $label.hide();
    }
  );

  $("#hover-new").click(function (e) {
    if (!$(e.target).is("#hover-new")) return;
    closeModal();
    return false;
  });

  $("#hover-new-header .close").click(function () {
    closeModal();
    return false;
  });

  $(".hover").each(function () {
    $(this).click(function () {
      $("html, body").animate({ scrollTop: 0 });

      var $el = $(this);
      $("body").addClass("show-modal");
      setTimeout(function () {
        $(".modal-bg").addClass("on");
      }, 1);
      $("#modal-content").append($(".modal").children());
      $el.addClass("current");
      if (!$el.is(".no-move")) {
        $(".overlay-item").append($el);
      }
      var pos = $.extend(
        {
          bottom: "auto",
          left: "auto",
          right: "auto",
          top: "auto",
          "transform-origin": "100% 100%",
        },
        $el.data("position")
      );
      $(".modal").append($("#hover-" + $el.data("hover")));
      $(".modal").addClass("is-" + $el.data("hover"));
      $(".modal").css(pos);

      $("#hover-new-header strong").text($(this).data("title"));
      $("#hover-new-header .link").toggle(!!$(this).data("link-text"));
      $("#hover-new-header .link").text($(this).data("link-text"));
      $("#hover-new-header .link").attr("href", $(this).data("link"));
      $("#hover-new-header").addClass("on");

      setTimeout(function () {
        $(".modal").addClass("opening");
        if (animations[$el.data("hover")]) {
          animations[$el.data("hover")]();
        }
      }, 0);
      setTimeout(function () {
        $(".modal").addClass("opened");

        /*
        $(".close-modal")
          .css({
            left: $(".modal").offset().left + $(".modal").width(),
            top: $(".modal").offset().top,
          })
          .fadeIn();
      */
      }, 300);
    });

    $(this).hover(
      function () {
        //if (!$(this).is(".whiteboard")) return;
        var $label = $("." + $(this).data("hover") + "-label");
        animateArrow($label);
      },
      function () {
        var $label = $("." + $(this).data("hover") + "-label");
        $label.hide();
      }
    );
  });

  $(".window").hover(
    function () {
      if ($("body").is(".show-window")) return;

      var $label = $(".window-label");
      animateArrow($label);
    },
    function () {
      var $label = $(".window-label");
      $label.hide();
    }
  );

  function animateArrow($label) {
    if (!$label || $("body").is(".preload")) return;

    $label.show();
    var path = MorphSVGPlugin.pathDataToBezier($label.find(".arrow-path"));

    TweenMax.fromTo(
      $label.find(".arrow-head"),
      0.3,
      { rotation: "90", scale: 0.5 },
      {
        rotation: "+0",
        scale: 1,
        bezier: { values: path, type: "cubic" },
        ease: Linear.easeIn,
        repeat: 0,
      }
    );
    TweenMax.fromTo(
      $label.find(".arrow-body"),
      0.3,
      { drawSVG: "0%" },
      { drawSVG: "100%", ease: Linear.easeIn, repeat: 0 }
    );
  }

  var musicInterval;

  const animationsOff = {
    music() {
      clearInterval(musicInterval);
    },
  };

  const animations = {
    dribbble() {
      var tl = new TimelineMax({
        repeat: 0,
        delay: 0,
      });
      tl.fromTo(
        $("#dribbble path"),
        2.4,
        {
          drawSVG: "0% 0%",
        },
        {
          ease: Power1.easeInOut,
          drawSVG: "0% 100%",
        },
        "0"
      );
      tl.fromTo(
        $("#dribbble circle"),
        0.8,
        {
          transformOrigin: "50% 50%",
          opacity: 0,
          y: 200,
        },
        {
          transformOrigin: "50% 50%",
          opacity: 1,
          ease: Elastic.easeOut.config(1.5, 0.3),
          y: 0,
        },
        "0.8"
      );
      tl.fromTo(
        $("#dn polyline"),
        1.0,
        {
          drawSVG: "0% 0%",
        },
        {
          ease: Power1.easeInOut,
          drawSVG: "0% 100%",
        },
        "2"
      );
      tl.fromTo(
        "#dn circle",
        1,
        {
          transformOrigin: "50% 50%",
          scale: 0,
        },
        {
          transformOrigin: "50% 50%",
          scale: 1,
          ease: Elastic.easeOut.config(1.7, 0.3),
        },
        "2.8"
      );
    },

    music() {
      var $mbar = $(".m-bar");
      musicInterval = setInterval(animate, 600);
      animate();
      function animate() {
        for (var i = 0; i <= 25; i++) {
          var $el = $mbar.eq(i);
          var $el2 = $mbar.eq(50 - i);
          var r = Math.random() * (i * 2.8) + 10;
          $el.height(r);
          $el2.height(r);
        }
      }
    },

    foursquare() {
      var tl = new TimelineMax({
        repeat: 0,
        delay: 0.8,
      });

      tl.fromTo(
        "#map-Page-1",
        0.4,
        {
          transformOrigin: "50% 50%",
          y: -240,
        },
        {
          transformOrigin: "50% 50%",
          y: 0,
          ease: Power1.easeIn,
        },
        "0"
      );

      tl.fromTo(
        "#map-Page-1",
        0.3,
        {
          transformOrigin: "50% 100%",
          scaleX: 1,
          scaleY: 1,
        },
        {
          transformOrigin: "50% 100%",
          scaleX: 1.34,
          scaleY: 0.5,
          ease: Power1.easeIn,
        },
        "0.2"
      );
      tl.fromTo(
        "#map-Page-1",
        0.3,
        {
          transformOrigin: "50% 100%",
        },
        {
          transformOrigin: "50% 100%",
          scaleX: 1,
          scaleY: 1,
          ease: Power1.easeOut,
        },
        ".5"
      );

      tl.fromTo(
        "#map-bg",
        0.8,
        {
          transformOrigin: "50% 50%",
          scale: 0,
        },
        {
          transformOrigin: "50% 50%",
          scale: 1,
          ease: Back.easeOut.config(1.7),
        },
        "-=0.4"
      );
      tl.fromTo(
        "#map-shadow",
        1,
        {
          transformOrigin: "50% 50%",
          scale: 0,
        },
        {
          transformOrigin: "50% 50%",
          scale: 1,
          ease: Back.easeOut.config(2),
        },
        "-=0.8"
      );

      tl.fromTo(
        $("#map-path-1"),
        0.4,
        {
          drawSVG: "0% 0%",
        },
        {
          ease: Power1.easeIn,
          drawSVG: "0% 100%",
        },
        "-=0.8"
      );

      tl.fromTo(
        $("#map-Oval-grass")[0],
        0.4,
        {
          opacity: 0,
        },
        {
          opacity: 1,
          ease: Power1.easeIn,
        },
        "-=0.6"
      );

      TweenLite.set("#map", {
        visibility: "visible",
      });

      var first = true;

      tl.add("maps", "-=.8");

      for (var i = 1; i <= 15; i++) {
        var $el = $("#map-road" + i);

        tl.fromTo(
          $el[0],
          0.4,
          {
            drawSVG: "0% 0%",
          },
          {
            ease: Power1.easeIn,
            drawSVG: "0% 100%",
          },
          "maps+=" + 0.05 * i
        );
        first = false;
      }

      tl.fromTo(
        $("#map-Path-4")[0],
        0.4,
        {
          opacity: 0,
        },
        {
          opacity: 1,
          ease: Power1.easeIn,
        },
        "-=0.5"
      );
      tl.fromTo(
        $("#map-Path-3")[0],
        0.4,
        {
          opacity: 0,
        },
        {
          opacity: 1,
          ease: Power1.easeIn,
        },
        "-=0.45"
      );
    },
  };

  /*
  var i = 1;
  $('.greg').click(function() {
    i++;
    $('.slider').addClass(`scene${i}`);
    setTimeout(function() {
      i++;
      $('.slider').addClass(`scene${i}`);
    }, 5000)
  });
  */

  var scene = 1;
  var showControl = false;
  $(".window").click(function () {
    var $label = $(".window-label");
    $label.hide();

    scene = 1;
    setTimeout(function () {
      $("body").on("click.window", function (e) {
        if (showControl) clearTimeout(showControl);
        if ($(e.target).closest(".window-left").length) return left();
        if ($(e.target).closest(".window-right").length) return right();
        if ($(e.target).closest(".window, .controls").length) return true;
        setTimeout(function () {
          $(".slider").removeClass("scene1 scene2 has-stork");
        }, 1000);
        $(".slider").addClass("scene3 setup");
        $("body").removeClass("show-window");
        $(".window").removeClass("on");
        $(".controls .wrapper").removeClass("show");
        $("body").off(".window");
        $(window).off(".window");
      });
    }, 100);

    $("body").removeClass("hide-window").addClass("show-window");
    $(this).addClass("on");
    $(".slider").removeClass("scene3");
    $(".slider").addClass("scene1");
    $(".slider").addClass("has-stork");
    $(".controls").addClass("show");
    $(".controls .loc").text("Jember, ID");
    $(".controls .dates").html("2005 &ndash; 2016");
    updateArrows();
    $(window).on("keyup.window", function (e) {
      if (e.keyCode === 37) {
        left();
      }
      if (e.keyCode === 39) {
        right();
      }
    });
    showControl = setTimeout(function () {
      $(".slider").removeClass("setup");
      $(".controls .wrapper").addClass("show");
    }, 4300);
  });

  var loc = {
    1: "Jember, ID",
    2: "Lumajang, ID",
    3: "Malang, ID",
  };

  var dates = {
    1: "2005 &ndash; 2016",
    2: "2016 &ndash; 2023",
    3: "2023 &ndash; Present",
  };

  function left() {
    if (scene <= 1) return false;
    $(".slider").addClass("reverse");
    $(".slider").removeClass(`scene${scene}`);
    scene--;
    $(".slider").addClass(`scene${scene}`);
    updateArrows();
    return false;
  }
  function right() {
    if (scene >= 3) return false;
    $(".slider").removeClass("reverse");
    $(".slider").removeClass(`scene${scene}`);
    scene++;
    $(".slider").addClass(`scene${scene}`);
    updateArrows();
    return false;
  }

  function updateArrows() {
    $(".controls .loc").text(loc[scene]);
    $(".controls .dates").html(dates[scene]);
    $(".window-left").toggleClass("disable", scene <= 1);
    $(".window-right").toggleClass("disable", scene >= 3);
  }
  //$('.window-left').click(left);
  //$('.window-right').click(right);

  $(".modal").hover(
    function () {
      $(".modal-bg").removeClass("modal-bg-hover");
      $(".close-modal").removeClass("close-modal-hover");
    },
    function () {
      $(".modal-bg").addClass("modal-bg-hover");
      $(".close-modal").addClass("close-modal-hover");
    }
  );

  var interval;
  $("#logo").hover(
    function () {
      var $els = $(".hover, .hover-new, .window");
      var i = 0;
      interval = setInterval(function () {
        var $el = $els.eq(i);
        i++;
        if (!$el.length) return clearInterval(interval);
        $el.addClass("hover-preview");
        var $label = $("." + $el.data("hover") + "-label");
        animateArrow($label);
      }, 80);
    },
    function () {
      if (interval) clearInterval(interval);
      //$('.tooltip').remove();
      $(".label").hide();
      $(".hover, .hover-new, .window").removeClass("hover-preview");
    }
  );
});

$(() => {
  if (!$("#timeline").length) return;
  Vue.use(VueLazyload, { preLoad: 2 });

  var app = new Vue({
    el: "#timeline",
    data() {
      return {
        timeline: [],
        isMobile: $(window).width() <= 899,
      };
    },

    mounted() {
      axios.get("/timeline").then((response) => {
        this.timeline = response.data;

        this.timeline.forEach((tl) => {
          tl.top_image = this.getTopImage(tl);
          tl.top_image_overlay = this.getTopImageOverlay(tl);
        });

        setTimeout(timelineScroll.getHighest, 10);
        setTimeout(timelineScroll.getHighest, 1000);
      });

      $(window).on("resize", () => {
        this.isMobile = $(window).width() <= 899;
      });

      // This will allow for sub-links to be clickable
      var oldUrl = false;
      $(document)
        .on("mouseover", ".faux-link", function () {
          oldUrl = $(this).closest(".tl").attr("href");
          $(this).closest(".tl").attr("href", $(this).attr("href"));
        })
        .on("mouseout", ".faux-link", function () {
          $(this).closest(".tl").attr("href", oldUrl);
        });
    },

    methods: {
      getImgSize(img) {
        if (this.isMobile) return { width: "auto", height: "auto" };

        const WIDTH = 380;
        const height = Math.floor((WIDTH * img.height) / img.width);
        return { width: `${WIDTH}px`, height: `${height}px` };
      },

      getTopImage(post) {
        if (!post.attachments || !post.attachments.length) return;

        for (let i = 0; i < post.attachments.length; i++) {
          if (post.attachments[i].filename.match(/\.(png|svg|jpg|jpeg|gif)/)) {
            if (
              !post.attachments[i].thumbnails ||
              post.attachments[i].filename.match(/svg/)
            ) {
              return post.attachments[i];
            }
            return post.attachments[i].thumbnails.large;
          }
        }

        return false;
      },

      getTopImageOverlay(post) {
        if (!post.attachments_overlay || !post.attachments_overlay.length)
          return;

        for (let i = 0; i < post.attachments_overlay.length; i++) {
          if (
            post.attachments_overlay[i].filename.match(
              /\.(png|svg|jpg|jpeg|gif)/
            )
          ) {
            if (
              !post.attachments_overlay[i].thumbnails ||
              post.attachments_overlay[i].filename.match(/svg/)
            ) {
              return post.attachments_overlay[i];
            }
            return post.attachments_overlay[i].thumbnails.large;
          }
        }
        return false;
      },

      getBottomImage(post) {
        if (!post.attachments_bottom || !post.attachments_bottom.length) return;

        const images = [];

        for (let i = 0; i < post.attachments_bottom.length; i++) {
          if (
            post.attachments_bottom[i].filename.match(
              /\.(png|svg|jpg|jpeg|gif)/
            )
          ) {
            if (JSON.stringify(post).match(/xkcd/))
              console.log(post.attachments_bottom[i]);
            images.push({
              thumbnail: post.attachments_bottom[i].thumbnails.large.url,
              url: post.attachments_bottom[i].url,
            });
          }
        }

        return images.length ? images : false;
      },

      parseTweet(post) {
        const tweet = (post.meta || "0/0").split("/");
        return { retweets: tweet[0], likes: tweet[1] };
      },

      parseUrls(post) {
        let links = [];

        /*
        if (post.url) {
          links.push({
            url: post.url,
            title: post.url_title,
          });
        }
        */

        if (post.more_urls) {
          links = links.concat(
            post.more_urls
              .trim()
              .split("\n")
              .map((url) => {
                const u = url.match(/\[(.*)\]\((.*)\)/);
                return { url: u[2], title: u[1] };
              })
          );
        }

        return links;
      },

      getIcon: (f) => {
        if (f.match(/youtube/)) return "fab fa-youtube";
        if (f.match(/reddit/)) return "fab fa-reddit";
        if (f.match(/twitter/)) return "fab fa-twitter";
        if (f.match(/github/)) return "fab fa-github";
        if (f.match(/producthunt/)) return "fab fa-product-hunt";
        if (f.match(/instagram/)) return "fab fa-instagram";
        if (f.match(/readme\.(com|io)/)) return "fab fa-readme";
        if (f.match(/apimixtape/)) return "far fa-cassette-tape";
        if (f.match(/mozillalifeboat/)) return "far fa-life-ring";
        if (f.match(/startupnotes/)) return "fas fa-book";
        if (f.match(/wapi/)) return "fas fa-boombox";
        if (
          f.match(/(softwareengineering|howibuilt|devjourney|podcast|mixergy)/)
        )
          return "fas fa-podcast";
        if (f.match(/(insider|forbes|techcrunch|guardian)/))
          return "far fa-newspaper";
        return "fas fa-link";
      },

      randomHeight() {
        return `${10 + Math.floor(Math.random() * 15)}px`;
      },

      md(t) {
        t = t.trim();
        t = t.replace(/\*\*(.*?)\*\*/g, "<b>$1</b>");
        t = t.replace(/__(.*?)__/g, "<b>$1</b>");
        t = t.replace(/\*(.*?)\*/g, "<i>$1</i>");
        t = t.replace(/~(.*?)~/g, "<strike>$1</strike>");
        t = t.replace(/\\_(.*?)\\_/g, "<i>$1</i>"); // Not sure why but sometimes we get \_ from airtable
        t = t.replace(/_(.*?)_/g, "<i>$1</i>");
        t = t.replace(
          /\[([^\]]+)\]\(([^\)]+)\)/g,
          '<span href="$2" class="faux-link" target="_new" @mouseover="console.log(1);">$1</span>'
        );
        return `<p>${t.replace(/\n+/g, "</p><p>")}</p>`;
      },

      getYear(d) {
        try {
          return d.split("-")[0];
        } catch (e) {
          return "";
        }
      },

      openLink(tl, $event) {
        if (tl.url.match(/(youtube|youtu.be)/)) {
          $event.preventDefault();
          lity(tl.url);
          return;
        }
      },

      openThumbnail(full_image, $event) {
        $event.stopPropagation();
        $event.preventDefault();
        lity(full_image);
        return;
      },
    },
  });

  timelineScroll.init();
});

function shuffle(array) {
  var currentIndex = array.length,
    temporaryValue,
    randomIndex;
  while (0 !== currentIndex) {
    randomIndex = Math.floor(Math.random() * currentIndex);
    currentIndex -= 1;
    temporaryValue = array[currentIndex];
    array[currentIndex] = array[randomIndex];
    array[randomIndex] = temporaryValue;
  }
  return array;
}

function start(page, time) {
  var $el = $("." + page);
  if ($el.data("bounce") === "up") {
    setTimeout(function () {
      $el.each(function () {
        TweenLite.to($(this), 1, {
          y: "0",
          ease: Elastic.easeOut.config(1, 0.75),
        });
        if ($(this).is(".fingers")) {
          $(this).addClass("on");
        }
      });
    }, time * 150 + 300);
  } else if ($el.data("bounce") === "up-slow") {
    setTimeout(function () {
      TweenLite.to($el, 1, {
        y: "0",
        opacity: 1,
        ease: Elastic.easeOut.config(1, 1),
      });
    }, time * 150 + 300);
  } else {
    setTimeout(function () {
      var tl = new TimelineMax();
      tl.to($el, 0.4, { scale: 1.1, opacity: 1 });
      tl.to($el, 0.1, { scale: 1, opacity: 1 });
    }, time * 150 + 300);
  }
}

function hide(page, time) {
  var $el = $("." + page);
  TweenLite.to($el, 0.4, {
    y: "260",
  });
}

function runAllAnimations() {
  console.log("MENJALANKAN FUNGSI ANIMASI UTAMA");

  // 3. (PENTING UNTUK DEBUG) Cek ukuran elemen kunci.
  // Harusnya ini sudah ada ukurannya (bukan 0).
  console.log("Ukuran container .greg saat ini:", $(".greg").width());

  if ($(".greg").width() === 0) {
    console.error(
      "ERROR: Layout belum siap, container utama masih berukuran 0!"
    );
    return; // Jangan jalankan animasi jika layout belum siap
  }

  $("#fidget").addClass("hide");
  $(".bg").addClass("on");

  start("whiteboard", 0);
  start("window-bounce", 1);
  start("github", 2);
  start("laptop", 3);
  start("greg-intro", 4);
  start("owlbert", 5);
  start("writing", 6);
  start("mug", 7);
  start("media", 8);
  start("headphones", 9);
  start("dribbble", 10);
  start("foursquare", 11);
  start("instagram", 12);
}

$(window).on("load", function () {
  console.log("Window.onload selesai. Semua aset sudah ter-download.");

  // Kita pakai jeda 500ms (setengah detik) untuk memastikan browser selesai 'melukis'
  setTimeout(runAllAnimations, 500);
  $("#fidget").addClass("hide");

  $(".bg").addClass("on");

  setTimeout(function () {
    $("body").removeClass("preload");
  }, 2000);

  console.log("loading...");

  setTimeout(function () {
    deferredAnimation.resolve();
  }, 1700);

  const signature = new Freezeframe($(".signature-light img")[0], {
    trigger: false,
  });
  $(window).on("signature", () => {
    $("#timeline").addClass("tl-ready");
    $("h1").css("opacity", 1);
    signature.start();
    setTimeout(function () {
      $("#timeline").addClass("tl-ready");
    }, 2000);
  });

  setTimeout(function () {
    twttr.widgets.load();
  }, 200);

  // Sort into timelines
  const $sort = () =>
    $(".work-left").height() < $(".work-right").height()
      ? $(".work-left")
      : $(".work-right");

  /*
  $(".work-item").each(function () {
    var $el = $(this);
    $sort().append($el);

    var $img = $el.find(".w-top img");
    if ($img.length && $img.attr("src").match(/gif/)) {
      const gif = new Freezeframe($el.find("img")[0], { trigger: false });
      $el.hover(
        () => gif.start(),
        () => gif.stop()
      );
    }
  });
  */

  /*
  var $slider = $(".tl-slider");
  var $timeline = $("#timeline");
  $(window).scroll(function () {
    const sticky = $timeline.offset().top - 30 < $(window).scrollTop();
    $slider.toggleClass("sticky", sticky);
  });
  */

  var eCount = 0;
  $(window).keypress((e) => {
    if (e.keyCode === 101) {
      eCount++;
      if (eCount >= 3) {
        eCount = 0;
        window.open(
          $("#edit").length
            ? $("#edit").val()
            : "https://airtable.com/apptqNCgqWPkNhlp0/tblHS00KS9jhInDFu/viwKUkCPmljIPl0MI"
        );
      }
      return;
    }
    eCount = 0;
  });
});

const timelineScroll = {
  highest: {},
  waypoints: [],

  init: () => {
    var self = this;
    $(window).scroll(function () {
      // TODO: Speed this up... or don't, I don't care
      var current = 0;
      if (self.waypoints) {
        for (var i = 0; i < self.waypoints.length; i++) {
          if (
            self.waypoints[i] < $(window).scrollTop() &&
            self.waypoints[i] > current
          ) {
            current = self.waypoints[i];
          }
        }

        $(".tl-slider-year").text(self.highest[current.toString()]);
      }

      // TODO: Definitely optimise this
      if (!deferredScroll.resolved) {
        if (
          $("h1").offset().top -
            $(window).scrollTop() -
            $(window).height() +
            100 <
          0
        ) {
          deferredScroll.resolve();
        }
      }
    });
    $(window).trigger("scroll");

    // TODO: Debounce
    $(window).resize(self.getHighest);
  },

  getHighest: function () {
    var self = this;
    var _highest = {};

    $(".tl-item").each(function () {
      if (
        !_highest[$(this).data("date")] ||
        $(this).offset().top < _highest[$(this).data("date")]
      ) {
        _highest[$(this).data("date")] = $(this).offset().top;
      }
    });

    self.highest = {
      0: new Date().getFullYear(),
    };
    for (v in _highest) {
      self.highest[_highest[v]] = v;
    }

    self.waypoints =
      Object.keys(self.highest)
        .map((v) => parseFloat(v, 10))
        .sort((a, b) => a - b) || [];
  },
};

class Deferred {
  constructor() {
    this.promise = new Promise((resolve, reject) => {
      this.reject = reject;
      this.resolve = () => {
        this.resolved = true;
        resolve();
      };
    });
  }
}

var deferredScroll = new Deferred();
var deferredAnimation = new Deferred();

var promises = [deferredScroll.promise, deferredAnimation.promise];

Promise.all(promises).then(function () {
  $(window).trigger("signature");
});

$(() => {
  if (!$("#logo").length) return;

  var tl = new TimelineMax({
    repeat: 0,
    delay: 0,
  });

  var circle = MorphSVGPlugin.convertToPath($("#stamp-circle")[0]);
  tl.fromTo(
    circle,
    0.3,
    {},
    { morphSVG: $("#stamp-stamp")[0], fill: "#04a3fa" },
    0
  );
  tl.fromTo(
    $("#stamp-l1"),
    0.3,
    { drawSVG: "50% 50%", opacity: 0 },
    { drawSVG: "0% 100%", opacity: 1 },
    0
  );
  tl.fromTo(
    $("#stamp-l2"),
    0.3,
    { drawSVG: "50% 50%", opacity: 0 },
    { drawSVG: "0% 100%", opacity: 1 },
    0
  );
  //tl.fromTo('#stamp-shade', 0.3, {opacity: 0}, { opacity: 1 }, 0);
  tl.pause();

  $("#logo").hover(
    () => {
      tl.play();
    },
    () => {
      tl.reverse();
    }
  );
});
