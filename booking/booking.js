document.addEventListener("DOMContentLoaded", () => {
    // Form Elements
    const form = document.querySelector("#booking-form");
    const centerSelect = document.querySelector("#booking-center-select");
    const centerInput = document.querySelector("#input-center");
    const locationInput = document.querySelector("#input-location");
    const hoursInput = document.querySelector("#input-hours");
    const dateInput = document.querySelector("#input-date");
    const dialysisTypeInput = document.querySelector("#input-dialysis-type");
    const sessionSelect = document.querySelector("#booking-session-select");
    const sessionInput = document.querySelector("#input-session");
    const timeInput = document.querySelector("#input-time");
    const patientNameInput = document.querySelector("#input-patient-name");
    const patientContactInput = document.querySelector("#input-patient-contact");
    const patientEmailInput = document.querySelector("#input-patient-email");
    const emergencyNameInput = document.querySelector("#input-emergency-name");
    const emergencyContactInput = document.querySelector("#input-emergency-contact");
    const notesInput = document.querySelector("#input-notes");
    const csrfToken = document.querySelector("#input-csrf-token")?.value ?? "";

    // UI Containers
    const centerBannerName = document.querySelector("#center-banner-name");
    const centerBannerLocation = document.querySelector("#center-banner-location");
    const centerBannerHours = document.querySelector("#center-banner-hours");
    const centerChangeBtn = document.querySelector("#center-change-btn");
    const dateCardsContainer = document.querySelector("#date-cards-container");
    const datePrevBtn = document.querySelector("#date-nav-prev");
    const dateNextBtn = document.querySelector("#date-nav-next");
    const dialysisTypeCards = [...document.querySelectorAll(".dialysis-type-card")];
    const timeSlotsContainer = document.querySelector("#time-slots-container");
    const continueBtn = document.querySelector("#btn-continue-review");
    const checklistHint = document.querySelector("#booking-checklist-hint");

    // Views
    const bookingMainView = document.querySelector("#booking-main-view");
    const bookingReviewView = document.querySelector("#booking-review-view");
    const bookingSuccessView = document.querySelector("#booking-success-view");

    // Review Fields
    const reviewCenter = document.querySelector("#review-center");
    const reviewLocation = document.querySelector("#review-location");
    const reviewType = document.querySelector("#review-type");
    const reviewDate = document.querySelector("#review-date");
    const reviewSession = document.querySelector("#review-session");
    const reviewTime = document.querySelector("#review-time");
    const reviewPatientName = document.querySelector("#review-patient-name");
    const reviewPatientContact = document.querySelector("#review-patient-contact");
    const reviewPatientEmail = document.querySelector("#review-patient-email");
    const reviewEmergencyName = document.querySelector("#review-emergency-name");
    const reviewEmergencyContact = document.querySelector("#review-emergency-contact");
    const reviewNotes = document.querySelector("#review-notes");
    const btnReviewBack = document.querySelector("#btn-review-back");
    const btnConfirmAppointment = document.querySelector("#btn-confirm-appointment");

    // Success Fields
    const successReference = document.querySelector("#success-reference");
    const successCenter = document.querySelector("#success-center");
    const successDateTime = document.querySelector("#success-datetime");
    const successType = document.querySelector("#success-type");

    // Summary Elements
    const summaryCenter = document.querySelector("#summary-center");
    const summaryLocation = document.querySelector("#summary-location");
    const summaryDateTime = document.querySelector("#summary-datetime");
    const summaryType = document.querySelector("#summary-type");
    const summarySession = document.querySelector("#summary-session");
    const summaryPatient = document.querySelector("#summary-patient");

    const ALL_TIME_SLOTS = [
        { value: "08:00", label: "8:00 AM", session: "Morning" },
        { value: "10:00", label: "10:00 AM", session: "Morning" },
        { value: "13:00", label: "1:00 PM", session: "Afternoon" },
        { value: "15:00", label: "3:00 PM", session: "Afternoon" },
        { value: "18:00", label: "6:00 PM", session: "Evening" }
    ];

    const DAYS_SHORT = ["SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT"];
    const MONTHS_SHORT = ["JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC"];
    const MONTHS_FULL = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

    let selectedTimeLabel = "";

    // Parse time strings like "8:00 am" or "5:00 pm" to minutes from midnight
    const parseTimeToMinutes = (timeStr) => {
        if (!timeStr) return null;
        const match = timeStr.trim().match(/(\d{1,2}):(\d{2})\s*(am|pm)/i);
        if (!match) return null;
        let hour = Number(match[1]);
        const min = Number(match[2]);
        const ampm = match[3].toLowerCase();
        if (ampm === "pm" && hour !== 12) hour += 12;
        if (ampm === "am" && hour === 12) hour = 0;
        return hour * 60 + min;
    };

    // Format YYYY-MM-DD to "September 10, 2026"
    const formatDateFriendly = (isoDate) => {
        if (!isoDate) return "";
        const [year, month, day] = isoDate.split("-").map(Number);
        if (!year || !month || !day) return isoDate;
        return `${MONTHS_FULL[month - 1]} ${day}, ${year}`;
    };

    // Generate Dynamic Date Cards (21 upcoming days starting today)
    const generateDateCards = () => {
        if (!dateCardsContainer) return;
        dateCardsContainer.replaceChildren();

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        for (let i = 0; i < 21; i++) {
            const date = new Date(today);
            date.setDate(today.getDate() + i);

            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, "0");
            const day = String(date.getDate()).padStart(2, "0");
            const isoString = `${year}-${month}-${day}`;

            const card = document.createElement("button");
            card.type = "button";
            card.className = "date-card";
            card.dataset.date = isoString;
            card.setAttribute("aria-label", `${DAYS_SHORT[date.getDay()]}, ${MONTHS_SHORT[date.getMonth()]} ${date.getDate()}, ${year}`);

            card.innerHTML = `
                <span class="date-day-name">${DAYS_SHORT[date.getDay()]}</span>
                <span class="date-day-number">${date.getDate()}</span>
                <span class="date-month-name">${MONTHS_SHORT[date.getMonth()]}</span>
            `;

            card.addEventListener("click", () => {
                selectDate(isoString, card);
            });

            dateCardsContainer.appendChild(card);
        }

        // Auto-select first date (today) by default
        const firstCard = dateCardsContainer.querySelector(".date-card");
        if (firstCard && !dateInput.value) {
            selectDate(firstCard.dataset.date, firstCard);
        }
    };

    const selectDate = (isoDate, targetCard) => {
        dateInput.value = isoDate;
        dateCardsContainer.querySelectorAll(".date-card").forEach((c) => c.classList.remove("is-selected"));
        if (targetCard) {
            targetCard.classList.add("is-selected");
        } else {
            const match = dateCardsContainer.querySelector(`[data-date="${isoDate}"]`);
            if (match) match.classList.add("is-selected");
        }
        // Reset selected time when date changes
        timeInput.value = "";
        selectedTimeLabel = "";
        updateSummary();
        renderTimeSlots();
    };

    // Scroll controls for date cards
    datePrevBtn?.addEventListener("click", () => {
        dateCardsContainer.scrollBy({ left: -240, behavior: "smooth" });
    });
    dateNextBtn?.addEventListener("click", () => {
        dateCardsContainer.scrollBy({ left: 240, behavior: "smooth" });
    });

    // Dialysis Type Selection
    dialysisTypeCards.forEach((card) => {
        card.addEventListener("click", () => {
            dialysisTypeCards.forEach((c) => c.classList.remove("is-selected"));
            card.classList.add("is-selected");
            const type = card.dataset.type;
            dialysisTypeInput.value = type;
            updateSummary();
            renderTimeSlots();
        });
    });

    // Session Selection
    sessionSelect?.addEventListener("change", () => {
        sessionInput.value = sessionSelect.value;
        timeInput.value = "";
        selectedTimeLabel = "";
        updateSummary();
        renderTimeSlots();
    });

    // Center Selection change
    const updateCenter = (centerName, location, hours) => {
        if (!centerName) return;
        centerInput.value = centerName;
        locationInput.value = location;
        hoursInput.value = hours;

        if (centerBannerName) centerBannerName.textContent = centerName;
        if (centerBannerLocation) centerBannerLocation.textContent = location;
        if (centerBannerHours) centerBannerHours.textContent = hours;

        if (centerSelect) centerSelect.value = centerName;

        timeInput.value = "";
        selectedTimeLabel = "";
        updateSummary();
        renderTimeSlots();
    };

    centerSelect?.addEventListener("change", () => {
        const option = centerSelect.selectedOptions[0];
        if (option && option.value) {
            updateCenter(option.value, option.dataset.location || "", option.dataset.hours || "");
        }
    });

    centerChangeBtn?.addEventListener("click", () => {
        if (centerSelect) {
            centerSelect.hidden = !centerSelect.hidden;
            if (!centerSelect.hidden) centerSelect.focus();
        }
    });

    // Fetch Booked Slots & Render Time Slots
    const renderTimeSlots = async () => {
        if (!timeSlotsContainer) return;
        timeSlotsContainer.replaceChildren();

        const center = centerInput.value;
        const date = dateInput.value;
        const session = sessionSelect.value;
        const hours = hoursInput.value;

        if (!center || !date) {
            timeSlotsContainer.innerHTML = `<p class="time-empty-message">Please select a dialysis center and date first.</p>`;
            validateForm();
            return;
        }

        if (!session) {
            timeSlotsContainer.innerHTML = `<p class="time-empty-message">Please select a session (Morning, Afternoon, or Evening) to view available times.</p>`;
            validateForm();
            return;
        }

        // Parse operating hours
        const hourMatches = hours.match(/\d{1,2}:\d{2}\s*(?:am|pm)/gi) ?? [];
        const opening = parseTimeToMinutes(hourMatches[0] ?? "8:00 am");
        const closing = parseTimeToMinutes(hourMatches[1] ?? "5:00 pm");

        let bookedTimes = [];
        try {
            const response = await fetch(`availability.php?center=${encodeURIComponent(center)}&date=${encodeURIComponent(date)}`);
            if (response.ok) {
                const data = await response.json();
                bookedTimes = data.bookedTimes ?? [];
                if (data.location && !locationInput.value) locationInput.value = data.location;
                if (data.hours && !hoursInput.value) hoursInput.value = data.hours;
            }
        } catch (error) {
            bookedTimes = [];
        }

        const filteredSlots = ALL_TIME_SLOTS.filter((slot) => {
            if (slot.session !== session) return false;
            const slotMinutes = parseTimeToMinutes(slot.label);
            if (opening !== null && closing !== null && slotMinutes !== null) {
                return slotMinutes >= opening && slotMinutes <= closing;
            }
            return true;
        });

        if (filteredSlots.length === 0) {
            timeSlotsContainer.innerHTML = `<p class="time-empty-message">No time slots are available during ${session} hours for this center.</p>`;
            validateForm();
            return;
        }

        filteredSlots.forEach((slot) => {
            const isBooked = bookedTimes.includes(slot.value);
            const isSelected = timeInput.value === slot.value;

            const btn = document.createElement("button");
            btn.type = "button";
            btn.className = `time-slot-btn ${isBooked ? "is-full" : "is-available"} ${isSelected ? "is-selected" : ""}`;
            btn.disabled = isBooked;
            btn.dataset.time = slot.value;
            btn.dataset.label = slot.label;

            btn.innerHTML = `
                <span>${slot.label}</span>
                <span class="time-slot-status-label">${isBooked ? "Fully Booked" : (isSelected ? "Selected" : "Available")}</span>
            `;

            if (!isBooked) {
                btn.addEventListener("click", () => {
                    timeSlotsContainer.querySelectorAll(".time-slot-btn").forEach((b) => {
                        b.classList.remove("is-selected");
                        const statusSpan = b.querySelector(".time-slot-status-label");
                        if (statusSpan && !b.classList.contains("is-full")) {
                            statusSpan.textContent = "Available";
                        }
                    });

                    btn.classList.add("is-selected");
                    const statusSpan = btn.querySelector(".time-slot-status-label");
                    if (statusSpan) statusSpan.textContent = "Selected";

                    timeInput.value = slot.value;
                    selectedTimeLabel = slot.label;
                    updateSummary();
                });
            }

            timeSlotsContainer.appendChild(btn);
        });

        validateForm();
    };

    // Update Right Column Live Summary
    const updateSummary = () => {
        // Center
        if (summaryCenter) {
            const cVal = centerInput.value;
            summaryCenter.textContent = cVal || "Not selected";
            summaryCenter.classList.toggle("is-empty", !cVal);
        }

        // Location
        if (summaryLocation) {
            const lVal = locationInput.value;
            summaryLocation.textContent = lVal || "Not selected";
            summaryLocation.classList.toggle("is-empty", !lVal);
        }

        // Date & Time
        if (summaryDateTime) {
            const dVal = dateInput.value;
            const tVal = selectedTimeLabel;
            if (dVal && tVal) {
                summaryDateTime.textContent = `${formatDateFriendly(dVal)} at ${tVal}`;
                summaryDateTime.classList.remove("is-empty");
            } else if (dVal) {
                summaryDateTime.textContent = `${formatDateFriendly(dVal)} (Select time)`;
                summaryDateTime.classList.remove("is-empty");
            } else {
                summaryDateTime.textContent = "Not selected";
                summaryDateTime.classList.add("is-empty");
            }
        }

        // Dialysis Type
        if (summaryType) {
            const dtVal = dialysisTypeInput.value;
            summaryType.textContent = dtVal || "Not selected";
            summaryType.classList.toggle("is-empty", !dtVal);
        }

        // Session
        if (summarySession) {
            const sVal = sessionSelect.value;
            summarySession.textContent = sVal || "Not selected";
            summarySession.classList.toggle("is-empty", !sVal);
        }

        // Patient
        if (summaryPatient) {
            const pVal = patientNameInput.value.trim();
            summaryPatient.textContent = pVal || "Not selected";
            summaryPatient.classList.toggle("is-empty", !pVal);
        }

        validateForm();
    };

    // Live Form Validation
    const validateForm = () => {
        const hasCenter = Boolean(centerInput.value.trim());
        const hasDate = Boolean(dateInput.value.trim());
        const hasType = Boolean(dialysisTypeInput.value.trim());
        const hasSession = Boolean(sessionSelect.value.trim());
        const hasTime = Boolean(timeInput.value.trim());
        const hasPatientName = Boolean(patientNameInput.value.trim());
        const hasPatientContact = Boolean(patientContactInput.value.trim());
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const hasPatientEmail = emailRegex.test(patientEmailInput.value.trim());
        const hasEmergencyName = Boolean(emergencyNameInput.value.trim());
        const hasEmergencyContact = Boolean(emergencyContactInput.value.trim());

        const isComplete = hasCenter && hasDate && hasType && hasSession && hasTime &&
                           hasPatientName && hasPatientContact && hasPatientEmail &&
                           hasEmergencyName && hasEmergencyContact;

        if (continueBtn) {
            continueBtn.disabled = !isComplete;
        }

        if (checklistHint) {
            if (!isComplete) {
                const missing = [];
                if (!hasCenter) missing.push("Center");
                if (!hasDate) missing.push("Date");
                if (!hasType) missing.push("Dialysis Type");
                if (!hasSession) missing.push("Session");
                if (!hasTime) missing.push("Time");
                if (!hasPatientName || !hasPatientContact || !hasPatientEmail) missing.push("Patient Info");
                if (!hasEmergencyName || !hasEmergencyContact) missing.push("Emergency Contact");
                checklistHint.textContent = `Please complete: ${missing.slice(0, 3).join(", ")}${missing.length > 3 ? "..." : ""}`;
            } else {
                checklistHint.textContent = "All required fields completed. Ready for review.";
            }
        }

        return isComplete;
    };

    // Attach listeners to input fields
    [patientNameInput, patientContactInput, patientEmailInput, emergencyNameInput, emergencyContactInput, notesInput].forEach((input) => {
        input?.addEventListener("input", updateSummary);
    });

    // Review Step Transition
    continueBtn?.addEventListener("click", () => {
        if (!validateForm()) return;

        // Populate Review screen
        if (reviewCenter) reviewCenter.textContent = centerInput.value;
        if (reviewLocation) reviewLocation.textContent = locationInput.value;
        if (reviewType) reviewType.textContent = dialysisTypeInput.value;
        if (reviewDate) reviewDate.textContent = formatDateFriendly(dateInput.value);
        if (reviewSession) reviewSession.textContent = sessionSelect.value;
        if (reviewTime) reviewTime.textContent = selectedTimeLabel;
        if (reviewPatientName) reviewPatientName.textContent = patientNameInput.value.trim();
        if (reviewPatientContact) reviewPatientContact.textContent = patientContactInput.value.trim();
        if (reviewPatientEmail) reviewPatientEmail.textContent = patientEmailInput.value.trim();
        if (reviewEmergencyName) reviewEmergencyName.textContent = emergencyNameInput.value.trim();
        if (reviewEmergencyContact) reviewEmergencyContact.textContent = emergencyContactInput.value.trim();
        if (reviewNotes) reviewNotes.textContent = notesInput.value.trim() || "None";

        bookingMainView.style.display = "none";
        bookingReviewView.classList.add("is-active");
        window.scrollTo({ top: 0, behavior: "smooth" });
    });

    // Back to Edit button
    btnReviewBack?.addEventListener("click", () => {
        bookingReviewView.classList.remove("is-active");
        bookingMainView.style.display = "block";
        window.scrollTo({ top: 0, behavior: "smooth" });
    });

    // Confirm Appointment Submission
    btnConfirmAppointment?.addEventListener("click", async () => {
        btnConfirmAppointment.disabled = true;
        const originalText = btnConfirmAppointment.textContent;
        btnConfirmAppointment.textContent = "Submitting Appointment...";

        const formData = new FormData(form);
        formData.append("format", "json");

        try {
            const response = await fetch("create.php", {
                method: "POST",
                body: formData,
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "Accept": "application/json",
                },
            });

            const result = await response.json();

            if (result.success && result.reference) {
                // Show Success Screen
                if (successReference) successReference.textContent = result.reference;
                if (successCenter) successCenter.textContent = centerInput.value;
                if (successDateTime) successDateTime.textContent = `${formatDateFriendly(dateInput.value)} at ${selectedTimeLabel}`;
                if (successType) successType.textContent = dialysisTypeInput.value;

                bookingReviewView.classList.remove("is-active");
                bookingSuccessView.classList.add("is-active");
                window.scrollTo({ top: 0, behavior: "smooth" });
            } else {
                alert(result.message || "We could not save your appointment request. Please try again.");
                btnConfirmAppointment.disabled = false;
                btnConfirmAppointment.textContent = originalText;
            }
        } catch (error) {
            alert("A network or server error occurred. Please try again.");
            btnConfirmAppointment.disabled = false;
            btnConfirmAppointment.textContent = originalText;
        }
    });

    // Initialize Page
    generateDateCards();
    updateSummary();

    // If center was passed via URL params or select
    const initialCenter = centerInput.value;
    if (initialCenter) {
        updateCenter(initialCenter, locationInput.value, hoursInput.value);
    }
});
