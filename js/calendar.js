document.addEventListener('DOMContentLoaded', function() {
    const calendar = document.getElementById('calendar');
    const slotsDiv = document.getElementById('slots');
    const generateSlotsButton = isAdmin ? document.getElementById('generate-slots-button') : null;
    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();
    let selectedDate = null;

    // Function to generate the calendar
    function generateCalendar(month, year) {
        calendar.innerHTML = '';

        const firstDay = new Date(year, month).getDay();
        const lastDate = new Date(year, month + 1, 0).getDate();
        const monthAndYear = document.getElementById('monthAndYear');
        monthAndYear.textContent = `${new Date(year, month).toLocaleString('default', { month: 'long' })} ${year}`;

        // Create a table for the calendar
        const table = document.createElement('table');
        const headerRow = document.createElement('tr');
        const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        // Generate header row for days of the week
        for (let day of daysOfWeek) {
            const th = document.createElement('th');
            th.textContent = day;
            headerRow.appendChild(th);
        }
        table.appendChild(headerRow);

        // Generate days of the month
        let date = 1;
        for (let i = 0; i < 6; i++) {
            const row = document.createElement('tr');

            for (let j = 0; j < 7; j++) {
                const cell = document.createElement('td');
                if (i === 0 && j < firstDay) {
                    cell.textContent = '';
                } else if (date > lastDate) {
                    break;
                } else {
                    cell.textContent = date;
                    date++;
                    cell.dataset.date = new Date(year, month, date).toISOString().split('T')[0];
                    cell.addEventListener('click', function(event) {
                        selectedDate = event.currentTarget.dataset.date;
                        loadSlots(new Date(selectedDate));
                    });
                }
                row.appendChild(cell);
            }

            table.appendChild(row);
        }

        calendar.appendChild(table);
    }

    // Function to load available slots for a selected date
    function loadSlots(date) {
        const formattedDate = date.toISOString().split('T')[0];
        console.log(`Loading slots for date: ${formattedDate}`);
        let fetchUrl = isAdmin ? `../patient/load_slots.php?date=${formattedDate}` : `load_slots.php?date=${formattedDate}`;
        fetch(fetchUrl)
            .then(response => response.json())
            .then(slots => {
                slotsDiv.innerHTML = '';
                let hasAvailableSlots = false;

                if (slots.length > 0) {
                    slots.forEach(slot => {
                        const slotDiv = document.createElement('div');
                        slotDiv.textContent = `${slot.Time} ---- ${slot.Status}`;
                        if (slot.Status === 'available') {
                            hasAvailableSlots = true;

                            const serviceDropdown = document.createElement('select');
                            serviceDropdown.className = 'service-dropdown'; //class name for styling
                            serviceDropdown.innerHTML = `
                                <option value="">Select Service</option>
                                <option value="Dental Bonding">Dental Bonding</option>
                                <option value="Teeth Cleaning">Teeth Cleaning</option>
                                <option value="Teeth Whitening">Teeth Whitening</option>
                                <option value="Tooth Extraction">Tooth Extraction</option>
                            `;

                            const bookButton = document.createElement('button');
                            bookButton.textContent = 'Book';
                            bookButton.addEventListener('click', () => bookSlot(date, slot.Time, serviceDropdown.value));

                            slotDiv.appendChild(serviceDropdown);
                            slotDiv.appendChild(bookButton);
                        }
                        slotsDiv.appendChild(slotDiv);
                    });
                } else {
                    const noSlotsDiv = document.createElement('div');
                    noSlotsDiv.textContent = 'No slots available.';
                    noSlotsDiv.className = 'no-slots'; // Add the class for styling
                    slotsDiv.appendChild(noSlotsDiv);
                }

                if (!hasAvailableSlots && generateSlotsButton) {
                    generateSlotsButton.style.display = 'block';
                } else if (generateSlotsButton) {
                    generateSlotsButton.style.display = 'none';
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Function to book a slot
    function bookSlot(date, time, service) {
        const formattedDate = date.toISOString().split('T')[0];
        // if user is patient
        if (!isAdmin) {
            if (service) {
                fetch('book_appointment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `date=${formattedDate}&time=${time}&service=${service}`
                })
                .then(response => response.text())
                .then(result => {
                    alert('Appointment booked successfully');
                    loadSlots(date); // Refresh slots after booking
                })
                .catch(error => console.error('Error:', error));
            } else {
                alert('Please select a service.');
            }
        } else {
            // redirect to add_appointment.php if is admin
            window.location.href = `add_appointment.php?date=${formattedDate}&time=${time}&service=${service}`;
        }
    }

    // Function to generate slots for the selected date
    function generateSlots() {
        if (!selectedDate) return;

        fetch('generate_slots.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `date=${selectedDate}`
        })
        .then(response => response.text())
        .then(result => {
            alert(result);
            loadSlots(new Date(selectedDate)); // Refresh slots after generating
        })
        .catch(error => console.error('Error:', error));
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
    document.getElementById('prevMonth').addEventListener('click', previousMonth);
    document.getElementById('nextMonth').addEventListener('click', nextMonth);
    if (generateSlotsButton) {      // if there is generate slot button, add event listener (validate if patient or admin)
        generateSlotsButton.addEventListener('click', generateSlots);
    }

    // Initial calendar generation
    generateCalendar(currentMonth, currentYear);
});
