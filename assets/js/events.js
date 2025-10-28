function formatEventDateTime(ev) {
  // Expect ev.date and optionally ev.endDate or ev.endTime
  if (!ev.date) return "";

  const start = new Date(ev.date);
  const end = ev.endDate ? new Date(ev.endDate) : null;

  // Format options for date and time
  const dateOptions = {
    weekday: "long",
    day: "2-digit",
    month: "long",
    year: "numeric",
  };
  const timeOptions = { hour: "numeric", minute: "2-digit", hour12: true };

  const dateStr = start.toLocaleDateString(undefined, dateOptions);
  const timeStr = start.toLocaleTimeString(undefined, timeOptions);

  // If end date/time exists
  let endTimeStr = "";
  if (end) {
    const sameDay = start.toDateString() === end.toDateString();
    endTimeStr = sameDay ? ` - ${end.toLocaleTimeString(undefined, timeOptions)}` : ` – ${end.toLocaleDateString(undefined, dateOptions)}, ${end.toLocaleTimeString(undefined, timeOptions)}`;
  }

  return `📅 ${dateStr}, ${timeStr}${endTimeStr}`;
}

jQuery(document).ready(function ($) {
  if (typeof elmEventsData === "undefined") return;

  const allEvents = Array.isArray(elmEventsData.events) ? elmEventsData.events : [];
  const $eventsList = $("#events-list");
  const $categories = $(".elm_categories_list__item");
  const $seeAllBtn = $(".elm-see-all-btn");

  // Container selector (the HTML you showed uses data-element="calendar")
  const $calendar = $("[data-element='calendar']");
  if (!$calendar.length) return;

  const $navPrev = $calendar.find(".calendar__nav-prev");
  const $navNext = $calendar.find(".calendar__nav-next");
  const $navCurrent = $calendar.find(".calendar__nav-current");
  const $daysContainer = $calendar.find(".calendar__days");

  // Category color palette (assigned dynamically by slug)
  const categoryColors = {};
  const colorPalette = ["#f27c37", "#d934e5", "#64e033", "#1e73be", "#f94144", "#f3722c", "#f8961e", "#90be6d", "#43aa8b"];
  let colorIndex = 0;

  // assign colors based on slugs (normalized)
  allEvents.forEach((ev) => {
    const cats = Array.isArray(ev.categories) ? ev.categories : [];
    cats.forEach((c) => {
      const k = String(c).toLowerCase();
      if (!categoryColors[k]) {
        categoryColors[k] = colorPalette[colorIndex % colorPalette.length];
        colorIndex++;
      }
    });
  });

  // helpers
  function toYMDLocal(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, "0");
    const d = String(date.getDate()).padStart(2, "0");
    return `${y}-${m}-${d}`;
  }

  function dateObjFromYMD(ymd) {
    // parse YYYY-MM-DD -> local Date at midnight
    return new Date(ymd + "T00:00:00");
  }

  function normalizeCat(s) {
    return s === undefined || s === null ? "" : String(s).toLowerCase();
  }

  // state: month being displayed (1st day of month)
  const today = new Date();
  let visibleDate = new Date(today.getFullYear(), today.getMonth(), 1);
  let activeCategory = "all"; // slug or "all"

  // render events list (below calendar)

  function renderEventsList(events) {
    $eventsList.empty();

    if (!events || events.length === 0) {
      $eventsList.html("<p class='elm_no_events_msg'>Nie znaleziono wydarzeń.</p>");
      return;
    }

    events.forEach((ev) => {
      const cats = Array.isArray(ev.categories) ? ev.categories.map((c) => normalizeCat(c)) : [];
      const categoryHTML = cats
        .map((cat) => {
          const color = categoryColors[cat] || "#ccc";
          return `<span class="elm_event_card__category" style="background-color:${color}">${cat}</span>`;
        })
        .join(" ");

      const dateObj = new Date(ev.date + "T00:00:00");
      const day = String(dateObj.getDate()).padStart(2, "0");
      const month = String(dateObj.getMonth() + 1).padStart(2, "0");

      const html = `
  <div class="elm_event_card">
    <div class="elm_event_card__header">
      <div class="elm_event_card__categories">
        ${categoryHTML}
      </div>
      <h2 class="elm_event_card__title js-elm-modal-trigger" data-event-id="${ev.id}">
        ${day}/${month} / ${ev.title}
      </h2>
    </div>

        <div class="elm_event_card__image_wrapper">
          <img src="${ev.image}" alt="${ev.title}">
        </div>

        <div class="elm_event_card__content">
          ${ev.excerpt ? `<div class="elm_event_card__excerpt">${ev.excerpt.length > 200 ? ev.excerpt.substring(0, 200) + "..." : ev.excerpt}</div>` : ""}
          <div class="elm_event_card__link js-elm-modal-trigger" data-event-id="${ev.id}">
            Details
            <svg width="36" height="6" viewBox="0 0 36 6" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M36 3L31 0.113249V5.88675L36 3ZM0 3.5H31.5V2.5H0V3.5Z" fill="currentColor"></path>
            </svg>
          </div>
        </div>
      </div>
    `;

      $eventsList.append(html);
    });

    // Modal click handler
    $(".js-elm-modal-trigger").on("click", function () {
      const id = $(this).data("event-id");
      const ev = events.find((e) => e.id === id);
      if (!ev) return;

      const cats = (ev.categories || []).map((c) => normalizeCat(c));
      const lastCat = cats.length ? cats[cats.length - 1] : "";
      const color = lastCat ? categoryColors[lastCat] : "transparent";

      // Remove "elm_modal--visible" from HTML template
      const modalHTML = `
  <div class="elm_modal">
    <div class="elm_modal__placeholder" role="dialog">
      <div class="elm_modal__body">
        <div class="elm_modal__header">
          <div class="elm_modal__header_inner">
            <div class="elm_modal__category">
              <div class="elm_modal__category_name" style="background-color:${color}">${lastCat}</div>
            </div>
          <h2 class="elm_modal__title elm_modal__title--decor-left">${ev.title}</h2>

          </div>
          <div class="elm_modal__close" data-element="close">x</div>
        </div>

        <div class="elm_modal__columns elm_modal__columns--main">
          <div class="elm_modal__column elm_modal__column--left">
    
         
            <p class="elm_date-location"><strong>${formatEventDateTime(ev)}</strong></p>
          ${ev.location ? `<p class="elm_date-location">📍 <a class="elm_no-underline" href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(ev.location)}" target="_blank" rel="noopener">${ev.location}</a></p>` : ""}

            ${ev.description ? `<p>${ev.description}</p>` : ""}
            ${ev.website ? `<p>More info: <a href="${ev.website}" target="_blank">${ev.website}</a></p>` : ""}
          </div>

          <div class="elm_modal__column elm_modal__column--right elm_modal__column--gallery">
            ${
              ev.images && ev.images.length
                ? ev.images
                    .map(
                      (img) => `
              <div class="elm_modal__slide">
             
                  <img src="${img}" class="elm_modal__image" loading="lazy">
                
              </div>
            `
                    )
                    .join("")
                : `<img src="${ev.image}" class="elm_modal__image" />`
            }
          </div>
        </div>
      </div>
    </div>
  </div>
`;

      $("body").append(modalHTML);

      // Reference to modal
      const $modal = $(".elm_modal");

      // 👇 Force reflow before adding class (enables transition)
      void $modal[0].offsetWidth;

      // Now trigger the fade-in
      $modal.addClass("elm_modal--visible");

      // Close modal function with fade-out
      function closeModal() {
        $modal.removeClass("elm_modal--visible");
        setTimeout(() => $modal.remove(), 300); // match transition
      }

      // Close modal on button
      $(".elm_modal__close").on("click", (e) => {
        e.preventDefault();
        closeModal();
      });

      // Prevent closing when clicking inside modal
      $(".elm_modal__placeholder").on("click", (e) => e.stopPropagation());

      // Close on overlay click
      $modal.on("click", () => closeModal());
    });
  }

  // return events filtered by month/year and category
  function eventsForMonth(year, month, category = "all") {
    return allEvents.filter((ev) => {
      if (!ev.date) return false;
      const d = dateObjFromYMD(ev.date);
      const sameMonth = d.getFullYear() === year && d.getMonth() === month;
      if (!sameMonth) return false;
      if (category === "all") return true;
      const cats = (ev.categories || []).map((c) => normalizeCat(c));
      return cats.includes(category);
    });
  }

  // create day cells for visibleDate month
  function renderCalendarGrid() {
    const year = visibleDate.getFullYear();
    const month = visibleDate.getMonth(); // 0-based
    const monthLabel = visibleDate.toLocaleString("pl-PL", { month: "long", year: "numeric" }).replace(/^\p{Ll}/u, (c) => c.toUpperCase()); // capitalize first letter

    $navCurrent.text(monthLabel);

    $daysContainer.empty();

    // first day and number of days
    const firstDay = new Date(year, month, 1);
    // shift so week starts Monday (0=Sun -> 6)
    const startOffset = firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1;
    const lastDay = new Date(year, month + 1, 0);
    const totalDays = lastDay.getDate();

    // add leading empty cells
    for (let i = 0; i < startOffset; i++) {
      $daysContainer.append(`<div class="calendar__day"></div>`);
    }

    // events in this month (respecting activeCategory)
    const monthEvents = eventsForMonth(year, month, activeCategory);

    for (let d = 1; d <= totalDays; d++) {
      const ymd = `${year}-${String(month + 1).padStart(2, "0")}-${String(d).padStart(2, "0")}`;
      const eventsOnDay = monthEvents.filter((ev) => ev.date === ymd);

      let styleAttr = "";
      if (eventsOnDay.length) {
        // last event's last category color
        const lastEvent = eventsOnDay[eventsOnDay.length - 1];
        const cats = (lastEvent.categories || []).map((c) => normalizeCat(c));
        const lastCat = cats.length ? cats[cats.length - 1] : null;
        const color = lastCat ? categoryColors[lastCat] : null;
        if (color) {
          styleAttr = ` style="--day-background: ${color};" data-day="${d}" data-date="${ymd}"`;
        } else {
          styleAttr = ` data-day="${d}" data-date="${ymd}"`;
        }
      } else {
        styleAttr = ` data-day="${d}" data-date="${ymd}"`;
      }

      $daysContainer.append(`<div class="calendar__day"${styleAttr}>${d}</div>`);
    }

    // disable prev when visible month is the current month
    const isVisibleCurrentMonth = year === today.getFullYear() && month === today.getMonth();
    if (isVisibleCurrentMonth) {
      $navPrev.addClass("disabled").css("pointer-events", "none").attr("aria-disabled", "true");
    } else {
      $navPrev.removeClass("disabled").css("pointer-events", "auto").removeAttr("aria-disabled");
    }
  }

  // clicking a day -> show events for that date (respecting activeCategory)
  $daysContainer.on("click", ".calendar__day[data-day]", function () {
    const dateStr = $(this).attr("data-date");
    const filtered = allEvents.filter((ev) => ev.date === dateStr && (activeCategory === "all" || (ev.categories || []).map((c) => normalizeCat(c)).includes(activeCategory)));
    renderEventsList(filtered);
  });

  // nav handlers
  $navNext.on("click", function (e) {
    e.preventDefault();
    visibleDate = new Date(visibleDate.getFullYear(), visibleDate.getMonth() + 1, 1);
    renderCalendarGrid();
    // auto render events for that month
    const mEvents = eventsForMonth(visibleDate.getFullYear(), visibleDate.getMonth(), activeCategory);
    renderEventsList(mEvents);
  });

  $navPrev.on("click", function (e) {
    e.preventDefault();
    const prev = new Date(visibleDate.getFullYear(), visibleDate.getMonth() - 1, 1);
    const minAllowed = new Date(today.getFullYear(), today.getMonth(), 1); // don't go before current month
    if (prev < minAllowed) {
      return; // no nav
    }
    visibleDate = prev;
    renderCalendarGrid();
    const mEvents = eventsForMonth(visibleDate.getFullYear(), visibleDate.getMonth(), activeCategory);
    renderEventsList(mEvents);
  });

  // category click handling
  $categories.on("click", function () {
    $categories.removeClass("elm_categories_list__item--active");
    $(this).addClass("elm_categories_list__item--active");

    const raw = $(this).data("category");
    const norm = normalizeCat(raw || "all");
    activeCategory = norm === "" ? "all" : norm;

    // rebuild calendar and event list for new category
    renderCalendarGrid();
    const mEvents = eventsForMonth(visibleDate.getFullYear(), visibleDate.getMonth(), activeCategory);
    renderEventsList(mEvents);
  });

  $seeAllBtn.on("click", function (e) {
    e.preventDefault();
    activeCategory = "all";
    const mEvents = eventsForMonth(visibleDate.getFullYear(), visibleDate.getMonth(), activeCategory);
    renderEventsList(mEvents);
  });

  // initial render
  renderCalendarGrid();
  // render events of initial month
  const initialMonthEvents = eventsForMonth(visibleDate.getFullYear(), visibleDate.getMonth(), activeCategory);
  renderEventsList(initialMonthEvents);
});
