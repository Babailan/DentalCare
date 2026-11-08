document.addEventListener("DOMContentLoaded", function () {
  const calendar = document.getElementById("calendar");
  const slotsDiv = document.getElementById("slots");
  const generateSlotsButton = isAdmin
    ? document.getElementById("generate-slots-button")
    : null;
  const servicesRequest = fetch("../services_api.php")
    .then((response) => {
      if (!response.ok) throw new Error("Could not load appointment types.");
      return response.json();
    });
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
    const slotsRequest = fetch(fetchUrl)
      .then((response) => {
        if (!response.ok) throw new Error("Could not load available times.");
        return response.json();
      })
      .then((slots) => {
        if (!isAdmin || formattedDate < todayISO || slots.length === 0) {
          return slots;
        }

        const availableTimes = new Set(slots.map((slot) => slot.Time.slice(0, 5)));
        const hasMissingTimes = Array.from({ length: 17 }, (_, index) => {
          const minutes = 9 * 60 + index * 30;
          return `${pad(Math.floor(minutes / 60))}:${pad(minutes % 60)}`;
        }).some((time) => !availableTimes.has(time));

        if (!hasMissingTimes) return slots;

        return fetch("generate_slots.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: new URLSearchParams({ date: formattedDate }),
        }).then((response) => {
          if (!response.ok) {
            throw new Error("Could not generate the missing appointment times.");
          }
          return fetch(fetchUrl).then((slotsResponse) => {
            if (!slotsResponse.ok) {
              throw new Error("Could not reload appointment times.");
            }
            return slotsResponse.json();
          });
        });
      });

    Promise.all([slotsRequest, servicesRequest])
      .then(([slots, services]) => {
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
            const isBlocked = Number(slot.IsBlocked) === 1;

            const statusBadge = document.createElement("div");
            statusBadge.className = isAvailable
              ? "badge badge-success"
              : "badge badge-neutral";
            statusBadge.textContent = isAdmin
              ? (isBlocked ? "Blocked by clinic" : isAvailable ? "Available" : "Booked")
              : isAvailable ? "Available" : "Unavailable";

            slotInfo.appendChild(timeText);
            slotInfo.appendChild(statusBadge);
            if (isAdmin && slot.BookedBy) {
              const bookedByText = document.createElement("div");
              bookedByText.className = "text-sm text-base-content/70";
              bookedByText.textContent = `Booked by: ${slot.BookedBy}`;
              slotInfo.appendChild(bookedByText);
            }
            if (isAdmin && slot.PendingRequests) {
              const pendingText = document.createElement("div");
              pendingText.className = "text-sm text-base-content/70";
              pendingText.textContent = `Pending requests: ${slot.PendingRequests}`;
              slotInfo.appendChild(pendingText);
            }
            cardBody.appendChild(slotInfo);

            if (isAdmin && (isAvailable || isBlocked)) {
              const availabilityButton = document.createElement("button");
              availabilityButton.type = "button";
              availabilityButton.className = "btn btn-sm btn-outline";
              availabilityButton.textContent = isBlocked
                ? "Make available"
                : "Make unavailable";
              availabilityButton.addEventListener("click", () =>
                changeSlotAvailability(slot.SlotID, isBlocked ? "unblock" : "block", date),
              );
              cardBody.appendChild(availabilityButton);
            }

            if (isAvailable) {
              hasAvailableSlots = true;

              if (services.length > 0) {
                const cardActions = document.createElement("div");
                cardActions.className = "card-actions flex flex-wrap";

                const serviceDropdown = document.createElement("select");
                serviceDropdown.className = "select select-sm";
                serviceDropdown.setAttribute("aria-label", "Appointment type");
                const placeholder = document.createElement("option");
                placeholder.value = "";
                placeholder.textContent = "Select appointment type";
                serviceDropdown.appendChild(placeholder);
                services.forEach((service) => {
                  const option = document.createElement("option");
                  option.value = service.ServiceID;
                  option.textContent = `${service.Name} — RM ${Number(service.Price).toFixed(2)}`;
                  serviceDropdown.appendChild(option);
                });

                let selectedPatientId = "";
                if (isAdmin) {
                  const patientPicker = document.createElement("div");
                  patientPicker.className = "relative min-w-56 flex-1";

                  const patientSearch = document.createElement("input");
                  patientSearch.type = "search";
                  patientSearch.className = "input input-sm w-full";
                  patientSearch.placeholder = "Search patient by name or phone";
                  patientSearch.setAttribute("aria-label", "Search patient by name or phone");
                  patientSearch.setAttribute("aria-autocomplete", "list");
                  patientSearch.setAttribute("aria-expanded", "false");

                  const patientResults = document.createElement("div");
                  patientResults.className =
                    "absolute left-0 right-0 top-full z-20 mt-1 max-h-56 overflow-y-auto rounded-box border border-base-300 bg-base-100 p-1 shadow-lg";
                  patientResults.setAttribute("role", "listbox");
                  patientResults.hidden = true;

                  patientSearch.addEventListener("input", () => {
                    selectedPatientId = "";
                    patientResults.replaceChildren();
                    const query = patientSearch.value.trim().toLocaleLowerCase();
                    patientResults.hidden = query.length === 0;
                    patientSearch.setAttribute("aria-expanded", String(!patientResults.hidden));
                    if (patientResults.hidden) return;

                    const matches = adminPatients.filter((patient) =>
                      `${patient.Name} ${patient.MobileNo || ""}`
                        .toLocaleLowerCase()
                        .includes(query),
                    );
                    if (matches.length === 0) {
                      const noResults = document.createElement("p");
                      noResults.className = "p-2 text-sm";
                      noResults.textContent = "No matching patients.";
                      patientResults.appendChild(noResults);
                      return;
                    }

                    matches.forEach((patient) => {
                      const result = document.createElement("button");
                      result.type = "button";
                      result.className = "btn btn-ghost btn-sm w-full justify-start";
                      result.setAttribute("role", "option");
                      result.textContent = `${patient.Name} (${patient.MobileNo || "No phone"})`;
                      result.addEventListener("click", () => {
                        selectedPatientId = patient.PatientID;
                        patientSearch.value = result.textContent;
                        patientResults.hidden = true;
                        patientSearch.setAttribute("aria-expanded", "false");
                      });
                      patientResults.appendChild(result);
                    });
                  });

                  patientSearch.addEventListener("keydown", (event) => {
                    if (event.key === "ArrowDown" && !patientResults.hidden) {
                      const firstResult = patientResults.querySelector("button");
                      if (firstResult) {
                        event.preventDefault();
                        firstResult.focus();
                      }
                    } else if (event.key === "Escape") {
                      patientResults.hidden = true;
                      patientSearch.setAttribute("aria-expanded", "false");
                    }
                  });

                  patientPicker.appendChild(patientSearch);
                  patientPicker.appendChild(patientResults);
                  cardActions.appendChild(patientPicker);
                }

                const bookButton = document.createElement("button");
                bookButton.type = "button";
                bookButton.className = "btn btn-primary btn-sm";
                bookButton.textContent = isAdmin ? "Book for patient" : "Book";
                bookButton.addEventListener("click", () =>
                  bookSlot(
                    date,
                    slot.Time,
                    serviceDropdown.value,
                    selectedPatientId,
                  ),
                );

                cardActions.appendChild(serviceDropdown);
                cardActions.appendChild(bookButton);
                cardBody.appendChild(cardActions);
              }
            }

            slotCard.appendChild(cardBody);
            slotsDiv.appendChild(slotCard);
          });
          if (services.length === 0) {
            const noServicesAlert = document.createElement("div");
            noServicesAlert.className = "alert alert-info";
            noServicesAlert.textContent = "The clinic has not added appointment types yet.";
            slotsDiv.appendChild(noServicesAlert);
          }
        } else {
          const noSlotsAlert = document.createElement("div");
          noSlotsAlert.className = "alert alert-info";
          noSlotsAlert.textContent = services.length === 0
            ? "The clinic has not added appointment types yet."
            : "No slots available.";
          slotsDiv.appendChild(noSlotsAlert);
        }

        if (slots.length === 0 && generateSlotsButton) {
          generateSlotsButton.style.display = "inline-flex";
        } else if (generateSlotsButton) {
          generateSlotsButton.style.display = "none";
        }
      })
      .catch((error) => {
        slotsDiv.innerHTML = "";
        const errorAlert = document.createElement("div");
        errorAlert.className = "alert alert-error";
        errorAlert.textContent = error.message || "Could not load appointment details.";
        slotsDiv.appendChild(errorAlert);
      });
  }

  function changeSlotAvailability(slotId, action, date) {
    fetch("manage_slot.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        slot_id: slotId,
        action,
      }),
    })
      .then(async (response) => {
        const result = await response.json();
        if (!response.ok) {
          throw new Error(result.error || "Could not update this slot.");
        }
        showToast(result.message, "success");
        loadSlots(date);
      })
      .catch((error) => showToast(error.message, "error"));
  }

  // Function to book a slot
  function bookSlot(date, time, serviceId, patientId) {
    const formattedDate = date.toISOString().split("T")[0];
    // if user is patient
    if (!isAdmin) {
      if (serviceId) {
        fetch("book_appointment.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: new URLSearchParams({
            date: formattedDate,
            time: time.slice(0, 5),
            service_id: serviceId,
          }),
        })
          .then(async (response) => {
            const result = await response.json();
            if (!response.ok) throw new Error(result.error || "Could not book this time.");
            showToast(result.message, "success");
            loadSlots(date);
          })
          .catch((error) => showToast(error.message, "error"));
      } else {
        showToast("Please select a service.", "error");
      }
    } else {
      // redirect to add_appointment.php if is admin
      if (!serviceId) {
        showToast("Please select an appointment type.", "error");
        return;
      }
      if (!patientId) {
        showToast("Please select a patient.", "error");
        return;
      }

      const form = document.createElement("form");
      form.method = "POST";
      form.action = "add_appointment.php";
      const fields = {
        date: formattedDate,
        time: time.slice(0, 5),
        service_id: serviceId,
        patient_id: patientId,
      };
      Object.entries(fields).forEach(([name, value]) => {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = name;
        input.value = value;
        form.appendChild(input);
      });
      document.body.appendChild(form);
      form.submit();
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
