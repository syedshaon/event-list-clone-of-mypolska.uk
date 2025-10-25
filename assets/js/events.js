jQuery(document).ready(function ($) {
  if (typeof elmEventsData === "undefined") return;

  const allEvents = elmEventsData.events || [];
  const $eventsList = $("#events-list");
  const $categories = $(".categories_list__item");

  // ---------- Assign category colors ----------
  const categoryColors = {};
  const colorPalette = ["#f94144", "#f3722c", "#f8961e", "#f9844a", "#f9c74f", "#90be6d", "#43aa8b", "#277da1", "#9b5de5"];
  let colorIndex = 0;

  allEvents.forEach((ev) => {
    ev.categories.forEach((cat) => {
      if (!categoryColors[cat]) {
        categoryColors[cat] = colorPalette[colorIndex % colorPalette.length];
        colorIndex++;
      }
    });
  });

  // ---------- Render Events ----------
  function renderEvents(events) {
    $eventsList.empty();

    if (events.length === 0) {
      $eventsList.html("<p>No events found for this selection.</p>");
      return;
    }

    events.forEach((event) => {
      const html = `
        <div class="event-item">
          <h3><a href="${event.link}">${event.title}</a></h3>
          <p><strong>Date:</strong> ${event.date}</p>
          ${event.time ? `<p><strong>Time:</strong> ${event.time}</p>` : ""}
          ${event.location ? `<p><strong>Location:</strong> ${event.location}</p>` : ""}
          <p>${event.excerpt}</p>
        </div>
      `;
      $eventsList.append(html);
    });
  }

  // ---------- Initial render (current month only) ----------
  const today = new Date();
  const currentMonthEvents = allEvents.filter((ev) => {
    const evDate = new Date(ev.date);
    return evDate.getMonth() === today.getMonth() && evDate.getFullYear() === today.getFullYear();
  });
  renderEvents(currentMonthEvents);

  // ---------- Category filter ----------
  $categories.on("click", function () {
    $categories.removeClass("categories_list__item--active");
    $(this).addClass("categories_list__item--active");

    const selected = $(this).data("category");
    const filtered = selected === "all" ? allEvents : allEvents.filter((ev) => ev.categories.includes(selected));

    renderEvents(filtered);
  });

  // ---------- Calendar setup ----------
  const calendarEl = document.getElementById("events-list-mashi-calendar");
  if (calendarEl && typeof FullCalendar !== "undefined") {
    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: "dayGridMonth",
      headerToolbar: {
        left: "prev",
        center: "title",
        right: "next",
      },
      validRange: function (nowDate) {
        const start = new Date(nowDate.getFullYear(), nowDate.getMonth(), 1);
        return { start: start };
      },
      events: [], // We'll color manually, not use built-in events display
      dateClick: function (info) {
        const dateStr = info.dateStr;
        const filtered = allEvents.filter((ev) => ev.date === dateStr);
        renderEvents(filtered);
      },
      datesSet: function (info) {
        // Filter event list by visible month
        const viewStart = new Date(info.start);
        const viewEnd = new Date(info.end);

        const filtered = allEvents.filter((ev) => {
          const evDate = new Date(ev.date);
          return evDate >= viewStart && evDate < viewEnd;
        });
        renderEvents(filtered);

        // Prevent navigating before current month
        const now = new Date();
        const currentMonth = now.getMonth();
        const currentYear = now.getFullYear();
        if (info.start.getFullYear() < currentYear || (info.start.getFullYear() === currentYear && info.start.getMonth() < currentMonth)) {
          calendar.gotoDate(now);
        }
      },
      // ---------- Color event dates ----------
      dayCellDidMount: function (arg) {
        // Get local date string (not UTC)
        const dateStr = arg.date.toLocaleDateString("en-CA"); // gives YYYY-MM-DD in local time zone
        const eventsOnDate = allEvents.filter((ev) => ev.date === dateStr);

        if (eventsOnDate.length > 0) {
          const lastEvent = eventsOnDate[eventsOnDate.length - 1];
          const lastCat = lastEvent.categories[lastEvent.categories.length - 1];
          const bgColor = categoryColors[lastCat] || "#ccc";

          const frame = arg.el.querySelector(".fc-daygrid-day-frame");
          if (frame) {
            frame.style.backgroundColor = bgColor;
            frame.style.color = "#fff";
            frame.style.borderRadius = "6px";
          }
        }
      },
    });

    calendar.render();
  }
});
