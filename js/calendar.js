document.addEventListener("DOMContentLoaded", function () {
  const calendar = document.getElementById("calendar");
  const slotsDiv = document.getElementById("slots");
  const generateSlotsButton = isAdmin
    ? document.getElementById("generate-slots-button")
    : null;
  let currentMonth = new Date().getMonth();
  let currentYear = new Date().getFullYear();
  let selectedDate = null;
  let selectedDayButton = null;

  // Style the slots container as a daisyUI stack
  slotsDiv.classList.add(
    "flex",
    "flex-col",
    "gap-3",
    "w-full",
    "max-w-2xl",
    "mx-auto",
  );
  // Local date helpers (avoids timezone shifts from toISOString)
  const pad = (n) => String(n).padStart(2, "0");
  const toISODate = (y, m, d) => `${y}-${pad(m + 1)}-${pad(d)}`;
  const todayISO = toISODate(
    new Date().getFullYear(),
    new Date().getMonth(),
    new Date().getDate(),
  );

  // Small daisyUI toast used instead of alert()
  function showToast(message, type) {
    const toast = document.createElement("div");
    toast.className = "toast toast-end z-50";

    const alertBox = document.createElement("div");
    alertBox.className = "alert alert-" + (type || "info");
    alertBox.setAttribute("role", "alert");
    alertBox.textContent = message;

    toast.appendChild(alertBox);
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
  }

  // Function to generate the calendar
  function generateCalendar(month, year) {
    calendar.innerHTML = "";

    const firstDay = new Date(year, month).getDay();
    const lastDate = new Date(year, month + 1, 0).getDate();
    const monthAndYear = document.getElementById("monthAndYear");
    monthAndYear.textContent = `${new Date(year, month).toLocaleString("default", { month: "long" })} ${year}`;

    // daisyUI table with a proper header and body
    const table = document.createElement("table");
    table.className = "table table-zebra w-full text-center";
    const daysOfWeek = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

    const thead = document.createElement("thead");
    const headerRow = document.createElement("tr");
    for (let day of daysOfWeek) {
      const th = document.createElement("th");
      th.className = "text-center";
      th.textContent = day;
      headerRow.appendChild(th);
    }
    thead.appendChild(headerRow);
    table.appendChild(thead);

    const tbody = document.createElement("tbody");
    let date = 1;
    for (let i = 0; i < 6 && date <= lastDate; i++) {
      const row = document.createElement("tr");

      for (let j = 0; j < 7; j++) {
        const cell = document.createElement("td");
        cell.className = "p-1";

        if (i === 0 && j < firstDay) {
          cell.textContent = "";
        } else if (date > lastDate) {
          // Pad the last row so the grid stays rectangular
          cell.textContent = "";
        } else {
          const day = date;
          const isoDate = toISODate(year, month, day);

          const dayButton = document.createElement("button");
          dayButton.type = "button";
          dayButton.className = "btn btn-ghost btn-sm w-full";
          dayButton.textContent = day;
          dayButton.dataset.date = isoDate;

          // Mark today
          if (isoDate === todayISO) {
            dayButton.classList.add(
              "font-bold",
              "underline",
              "underline-offset-4",
            );
          }

          // Restore the selected day after re-render
          if (isoDate === selectedDate) {
            dayButton.classList.remove("btn-ghost");
            dayButton.classList.add("btn-primary");
            selectedDayButton = dayButton;
          }

          dayButton.addEventListener("click", function (event) {
            const clickedButton = event.currentTarget;

            // Move the selected style from the old day to the new one
            if (selectedDayButton && selectedDayButton !== clickedButton) {
              selectedDayButton.classList.remove("btn-primary");
              selectedDayButton.classList.add("btn-ghost");
            }
            clickedButton.classList.remove("btn-ghost");
            clickedButton.classList.add("btn-primary");
            selectedDayButton = clickedButton;
            selectedDate = clickedButton.dataset.date;

            loadSlots(new Date(selectedDate));
          });

          cell.appendChild(dayButton);
          date++;
        }

        row.appendChild(cell);
      }

      tbody.appendChild(row);
    }

    table.appendChild(tbody);

    const wrapper = document.createElement("div");
    wrapper.className = "overflow-x-auto";
    wrapper.appendChild(table);
    calendar.appendChild(wrapper);
  }

  // Function to load available slots for a selected date
  function loadSlots(date) {
    const formattedDate = date.toISOString().split("T")[0];
    console.log(`Loading slots for date: ${formattedDate}`);

    // Build a readable date label from the ISO string (timezone-safe)
    const [year, month, day] = formattedDate.split("-").map(Number);
    const dateLabel = new Date(year, month - 1, day).toLocaleDateString(
      "default",
      {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
      },
    );

    let fetchUrl = isAdmin
      ? `../patient/load_slots.php?date=${formattedDate}`
      : `load_slots.php?date=${formattedDate}`;
    fetch(fetchUrl)
      .then((response) => response.json())
      .then((slots) => {
        slotsDiv.innerHTML = "";
        let hasAvailableSlots = false;

        const title = document.createElement("div");
        title.className = "text-lg font-bold";
        title.textContent = `Slots for ${dateLabel}`;
        slotsDiv.appendChild(title);

        if (slots.length > 0) {
          slots.forEach((slot) => {
            const slotCard = document.createElement("div");
            slotCard.className =
              "card card-sm card-border bg-base-100 shadow-sm";

            const cardBody = document.createElement("div");
            cardBody.className =
              "card-body flex-row flex-wrap items-center justify-between gap-3";

            const slotInfo = document.createElement("div");
            slotInfo.className = "flex flex-col gap-1";

            const timeText = document.createElement("div");
            timeText.className = "font-bold";
            timeText.textContent = slot.Time.slice(0, 5);

            const isAvailable = slot.Status === "available";

            const statusBadge = document.createElement("div");
            statusBadge.className = isAvailable
              ? "badge badge-success"
              : "badge badge-neutral";
            statusBadge.textContent = isAvailable
              ? "Available"
              : slot.Status.charAt(0).toUpperCase() + slot.Status.slice(1);

            slotInfo.appendChild(timeText);
            slotInfo.appendChild(statusBadge);
            cardBody.appendChild(slotInfo);

            if (isAvailable) {
              hasAvailableSlots = true;

              const cardActions = document.createElement("div");
              cardActions.className = "card-actions";

              const serviceDropdown = document.createElement("select");
              serviceDropdown.className = "select select-bordered select-sm";
              serviceDropdown.innerHTML = `
                                <option value="">Select Service</option>
                                <option value="Dental Bonding">Dental Bonding</option>
                                <option value="Teeth Cleaning">Teeth Cleaning</option>
                                <option value="Teeth Whitening">Teeth Whitening</option>
                                <option value="Tooth Extraction">Tooth Extraction</option>
                            `;

              const bookButton = document.createElement("button");
              bookButton.className = "btn btn-primary btn-sm";
              bookButton.textContent = "Book";
              bookButton.addEventListener("click", () =>
                bookSlot(date, slot.Time, serviceDropdown.value),
              );

              cardActions.appendChild(serviceDropdown);
              cardActions.appendChild(bookButton);
              cardBody.appendChild(cardActions);
            }

            slotCard.appendChild(cardBody);
            slotsDiv.appendChild(slotCard);
          });
        } else {
          const noSlotsAlert = document.createElement("div");
          noSlotsAlert.className = "alert alert-info";
          noSlotsAlert.textContent = "No slots available.";
          slotsDiv.appendChild(noSlotsAlert);
        }

        if (!hasAvailableSlots && generateSlotsButton) {
          generateSlotsButton.style.display = "inline-flex";
        } else if (generateSlotsButton) {
          generateSlotsButton.style.display = "none";
        }
      })
      .catch((error) => console.error("Error:", error));
  }

  // Function to book a slot
  function bookSlot(date, time, service) {
    const formattedDate = date.toISOString().split("T")[0];
    // if user is patient
    if (!isAdmin) {
      if (service) {
        fetch("book_appointment.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `date=${formattedDate}&time=${time}&service=${service}`,
        })
          .then((response) => response.text())
          .then((result) => {
            showToast("Appointment booked successfully", "success");
            loadSlots(date); // Refresh slots after booking
          })
          .catch((error) => console.error("Error:", error));
      } else {
        showToast("Please select a service.", "error");
      }
    } else {
      // redirect to add_appointment.php if is admin
      window.location.href = `add_appointment.php?date=${formattedDate}&time=${time}&service=${service}`;
    }
  }

  // Function to generate slots for the selected date
  function generateSlots() {
    if (!selectedDate) return;

    fetch("generate_slots.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `date=${selectedDate}`,
    })
      .then((response) => response.text())
      .then((result) => {
        showToast(
          result,
          result.toLowerCase().includes("error") ? "error" : "success",
        );
        loadSlots(new Date(selectedDate)); // Refresh slots after generating
      })
      .catch((error) => console.error("Error:", error));
  }

  // Function to go to the previous month
  function previousMonth() {
    currentMonth--;
    if (currentMonth < 0) {
      currentMonth = 11;
      currentYear--;
    }
    generateCalendar(currentMonth, currentYear);
  }

  // Function to go to the next month
  function nextMonth() {
    currentMonth++;
    if (currentMonth > 11) {
      currentMonth = 0;
      currentYear++;
    }
    generateCalendar(currentMonth, currentYear);
  }

  // Attach event listeners to the previous and next buttons
  document.getElementById("prevMonth").addEventListener("click", previousMonth);
  document.getElementById("nextMonth").addEventListener("click", nextMonth);
  if (generateSlotsButton) {
    // if there is generate slot button, add event listener (validate if patient or admin)
    generateSlotsButton.addEventListener("click", generateSlots);
  }

  // Initial calendar generation
  generateCalendar(currentMonth, currentYear);
});
