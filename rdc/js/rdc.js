function add(event) {
  event.preventDefault();
  $(".btn-submit").attr("disabled", "");

  $(".btn-submit-text").hide();
  $(".btn-submit-text-saved").hide();
  $(".btn-submit-spinner").show();

  const name = $("#name").val();
  const scheduledOn = $("#scheduledOn").val();

  const formData = new FormData();
  formData.append("name", name);
  formData.append("scheduledOn", scheduledOn);

  $.ajax({
    method: "POST",
    url: url("/rdc/api/add"),
    contentType: false,
    processData: false,
    data: formData,
    error: (xhr) => {
      let message = "An error occurred";
      try {
        const json = JSON.parse(xhr.responseText);
        message = json.message || message;
      } catch { }
      alert(message);
      $(".btn-submit-spinner").hide();
      $(".btn-submit").removeAttr("disabled");
      $(".btn-submit-text").show();
    },
    success: () => {
      $(".btn-submit-text").hide();
      $(".btn-submit-text-saved").show();
      $(".btn-submit-spinner").hide();

      setTimeout(() => {
        window.location.href = url("/rdc");
      }, 1000);
    },
  });

  return false;
}

function edit(id, event) {
  event.preventDefault();
  $(".btn-submit").attr("disabled", "");

  $(".btn-submit-text").hide();
  $(".btn-submit-text-saved").hide();
  $(".btn-submit-spinner").show();

  const name = $("#name").val();
  const scheduledOn = $("#scheduledOn").val();

  const formData = new FormData();
  formData.append("name", name);
  formData.append("scheduledOn", scheduledOn);

  $.ajax({
    method: "POST",
    url: url(`/rdc/api/edit?id=${id}`),
    contentType: false,
    processData: false,
    data: formData,
    error: (xhr) => {
      let message = "An error occurred";
      try {
        const json = JSON.parse(xhr.responseText);
        message = json.message || message;
      } catch { }
      alert(message);
      $(".btn-submit-spinner").hide();
      $(".btn-submit").removeAttr("disabled");
      $(".btn-submit-text").show();
    },
    success: () => {
      $(".btn-submit-text").hide();
      $(".btn-submit-text-saved").show();
      $(".btn-submit-spinner").hide();

      setTimeout(() => {
        window.location.href = url("/rdc");
      }, 1000);
    },
  });

  return false;
}

function showDeleteConfirmation(id) {
  $("#btn-yes").attr("data-id", id);
  $("#modal-delete").modal("show");
}

async function _delete() {
  const id = $("#btn-yes").attr("data-id");
  if (id == null) return;

  const response = await fetch(url(`/rdc/api/delete?id=${id}`), {
    method: "DELETE",
  });

  if (response.ok) {
    window.location.href = url("/rdc");
  } else {
    try {
      const errorJson = await response.json();
      alert(errorJson.message || "An error occurred while deleting.");
    } catch (e) {
      alert("An error occurred while deleting.");
    }
    $("#modal-delete").modal("hide");
  }
}

// =========================================
// Slots Management
// =========================================

let slotCounter = 0;

$(document).ready(function() {
    // If existingSlots is defined, render them with their allocations
    if (typeof existingSlots !== 'undefined' && existingSlots.length > 0) {
        existingSlots.forEach(function(slot) {
            // slotAllocations is keyed by slot DB id
            var alloc = (typeof slotAllocations !== 'undefined' && slotAllocations[slot.id])
                ? slotAllocations[slot.id]
                : { guides: [], externals: [], students: [] };
            var card = renderSlotAccordion(slot.name, slot.fromTime, slot.toTime, alloc);
            if (card) {
                // Trigger save so slot switches to view mode with allocations shown
                card.find('.btn-save-slot').trigger('click');
            }
        });
    }
});

function addNewSlot() {
    const name = $("#new-slot-name").val().trim();
    const fromTime = $("#new-slot-from").val();
    const toTime = $("#new-slot-to").val();
    
    if (!name || !fromTime || !toTime) {
        alert("Please fill in Slot Name, From Time, and To Time.");
        return;
    }
    
    if (fromTime >= toTime) {
        alert("From time must be before To time.");
        return;
    }
    
    renderSlotAccordion(name, fromTime, toTime);
    
    // Clear inputs
    $("#new-slot-name").val("");
    $("#new-slot-from").val("08:00");
    $("#new-slot-to").val("11:30");
}

function renderSlotAccordion(name, fromTime, toTime, existingAlloc) {
    slotCounter++;
    const slotId = slotCounter;
    
    let template = document.getElementById("slot-accordion-template").innerHTML;
    template = template.replace(/{SLOT_ID}/g, slotId);
    template = template.replace(/{SLOT_NAME}/g, name);
    template = template.replace(/{FROM_TIME}/g, fromTime);
    template = template.replace(/{TO_TIME}/g, toTime);
    
    $("#slots-accordion").append(template);
    
    const newAccordion = $(`#collapse-${slotId}`);
    const card = newAccordion.closest('.slot-accordion-card');

    card.find('.slot-name-input').on('input', function() {
        card.find('.slot-title-display').text($(this).val());
    });

    card.find('.slot-from-input, .slot-to-input').on('input change', function() {
        const from = card.find('.slot-from-input').val();
        const to = card.find('.slot-to-input').val();
        card.find('.slot-time-display').text(`${from} - ${to}`);
    });

    // If existing allocations provided, pre-populate rows with selected values
    if (existingAlloc) {
        existingAlloc.guides.forEach(function(g) {
            addEntityRow(newAccordion.find('.guide-container'), 'guide', g.id);
        });
        if (existingAlloc.guides.length === 0) {
            addEntityRow(newAccordion.find('.guide-container'), 'guide');
        }

        existingAlloc.externals.forEach(function(e) {
            addEntityRow(newAccordion.find('.external-container'), 'external', e.id);
        });
        if (existingAlloc.externals.length === 0) {
            addEntityRow(newAccordion.find('.external-container'), 'external');
        }

        existingAlloc.students.forEach(function(s) {
            addEntityRow(newAccordion.find('.student-container'), 'student', s.id);
        });
        if (existingAlloc.students.length === 0) {
            addEntityRow(newAccordion.find('.student-container'), 'student');
        }
    } else {
        // New slot — add blank rows
        addEntityRow(newAccordion.find('.guide-container'), 'guide');
        addEntityRow(newAccordion.find('.external-container'), 'external');
        addEntityRow(newAccordion.find('.student-container'), 'student');
    }
    
    return card;
}

function updateStudentTurns(container) {
    var turn = 1;
    container.find('.entity-row').each(function() {
        $(this).attr('data-turn', turn);
        $(this).find('.turn-badge').text('Turn ' + turn);
        turn++;
    });
}

function addEntityRow(btnOrContainer, type, selectedId) {
    let container;
    if (btnOrContainer instanceof jQuery) {
        container = btnOrContainer;
    } else {
        container = $(btnOrContainer).closest('.mb-3').find(`.${type}-container`);
    }
    
    const template = document.getElementById("entity-row-template").innerHTML;
    const $row = $(template);
    const select = $row.find('.entity-select');
    
    let list = [];
    if (type === 'guide') list = staffList;
    if (type === 'external') list = externalMembers;
    if (type === 'student') list = studentsList;
    
    if (list && list.length) {
        list.forEach(item => {
            const opt = $('<option></option>').attr('value', item.id).text(item.name);
            if (selectedId && parseInt(item.id) === parseInt(selectedId)) {
                opt.attr('selected', 'selected');
            }
            select.append(opt);
        });
    }
    
    if (type === 'student') {
        $row.find('.turn-badge-wrapper').removeClass('d-none');
    }
    
    // If pre-selected, store the data-val immediately
    if (selectedId) {
        $row.attr('data-val', selectedId);
    }
    
    $row.attr('data-entity-type', type);
    container.append($row);

    if (type === 'student') {
        updateStudentTurns(container);
    }
}

function removeEntityRow(btn) {
    const $row = $(btn).closest('.entity-row');
    const container = $row.closest('.student-container');
    $row.remove();
    if (container.length > 0) {
        updateStudentTurns(container);
    }
}

function removeSlotAccordion(btn) {
    if(confirm("Are you sure you want to delete this entire slot?")) {
        $(btn).closest('.slot-accordion-card').remove();
    }
}

function editSlot(btn) {
    const card = $(btn).closest('.slot-accordion-card');
    const accordionBody = card.find('.collapse');
    if(!accordionBody.hasClass('show')) {
        accordionBody.collapse('show');
    }
    
    card.find('.slot-view-mode').addClass('d-none');
    card.find('.slot-edit-mode').removeClass('d-none');
    
    card.find('.btn-edit-slot').addClass('d-none');
    card.find('.btn-save-slot').removeClass('d-none');
    
    card.find('.slot-name-input').focus();
}

function saveSlotAccordion(btn) {
    const card = $(btn).closest('.slot-accordion-card');
    
    const name = card.find('.slot-name-input').val().trim();
    const fromTime = card.find('.slot-from-input').val();
    const toTime = card.find('.slot-to-input').val();
    
    if (!name || !fromTime || !toTime) {
        alert("Slot Name, From Time, and To Time are required.");
        return;
    }
    if (fromTime >= toTime) {
        alert("From time must be before To time.");
        return;
    }
    
    card.find('.view-slot-name').text(name);
    card.find('.view-from-time').text(fromTime);
    card.find('.view-to-time').text(toTime);
    
    const guides = [];
    card.find('.guide-container .entity-row').each(function() {
        const select = $(this).find('.entity-select');
        const text = select.find('option:selected').text();
        const val = select.val() || $(this).attr('data-val');
        if(val && val !== "") {
            $(this).attr('data-val', val);
            guides.push(text);
        }
    });
    card.find('.view-guides').html(guides.length > 0 ? guides.join('<br>') : '<em class="text-muted">None</em>');
    
    const externals = [];
    card.find('.external-container .entity-row').each(function() {
        const select = $(this).find('.entity-select');
        const text = select.find('option:selected').text();
        const val = select.val() || $(this).attr('data-val');
        if(val && val !== "") {
            $(this).attr('data-val', val);
            externals.push(text);
        }
    });
    card.find('.view-externals').html(externals.length > 0 ? externals.join('<br>') : '<em class="text-muted">None</em>');
    
    const students = [];
    let turn = 1;
    card.find('.student-container .entity-row').each(function() {
        const select = $(this).find('.entity-select');
        const text = select.find('option:selected').text();
        const val = select.val() || $(this).attr('data-val');
        if(val && val !== "") {
            $(this).attr('data-val', val);
            $(this).attr('data-turn', turn);
            students.push(`<div class="d-flex justify-content-between align-items-center py-1"><span>${text}</span><span class="badge badge-primary">Turn ${turn}</span></div>`);
            turn++;
        }
    });
    card.find('.view-students').html(students.length > 0 ? students.join('') : '<em class="text-muted">None</em>');
    
    card.find('.slot-title-display').text(name);
    card.find('.slot-time-display').text(`${fromTime} - ${toTime}`);
    
    card.find('.slot-edit-mode').addClass('d-none');
    card.find('.slot-view-mode').removeClass('d-none');
    
    card.find('.btn-save-slot').addClass('d-none');
    card.find('.btn-edit-slot').removeClass('d-none');
}

function saveSlots(rdcId) {
    var slots = [];
    var valid = true;

    $("#slots-accordion .slot-accordion-card").each(function (i) {
        var name = $(this).find(".slot-name-input").val().trim();
        var fromTime = $(this).find(".slot-from-input").val();
        var toTime = $(this).find(".slot-to-input").val();

        if (!name) {
            alert("Slot " + (i + 1) + ": Slot name is required.");
            valid = false;
            return false;
        }
        if (!fromTime) {
            alert("Slot " + (i + 1) + ": From time is required.");
            valid = false;
            return false;
        }
        if (!toTime) {
            alert("Slot " + (i + 1) + ": To time is required.");
            valid = false;
            return false;
        }
        if (fromTime >= toTime) {
            alert("Slot " + (i + 1) + ": From time must be before To time.");
            valid = false;
            return false;
        }

        var guides = [];
        $(this).find(".guide-container .entity-row").each(function() {
            var val = $(this).find(".entity-select").val() || $(this).attr("data-val");
            if (val && val !== "") guides.push(parseInt(val));
        });

        var externals = [];
        $(this).find(".external-container .entity-row").each(function() {
            var val = $(this).find(".entity-select").val() || $(this).attr("data-val");
            if (val && val !== "") externals.push(parseInt(val));
        });

        var students = [];
        var turn = 1;
        $(this).find(".student-container .entity-row").each(function() {
            var val = $(this).find(".entity-select").val() || $(this).attr("data-val");
            if (val && val !== "") {
                var assignedTurn = $(this).attr("data-turn") ? parseInt($(this).attr("data-turn")) : turn;
                students.push({ studentId: parseInt(val), turnNumber: assignedTurn });
                turn++;
            }
        });

        slots.push({ 
            name: name, 
            fromTime: fromTime, 
            toTime: toTime,
            guides: guides,
            externals: externals,
            students: students
        });
    });

    if (!valid) return;

    $("#btn-save-slots").prop("disabled", true);
    $(".btn-save-text").hide();
    $(".btn-save-text-saved").hide();
    $(".btn-save-spinner").show();

    $.ajax({
        method: "POST",
        url: url("/rdc/api/save-slots?id=" + rdcId),
        contentType: "application/json",
        data: JSON.stringify({ slots: slots }),
        error: function (xhr) {
            var message = "An error occurred";
            try {
                var json = JSON.parse(xhr.responseText);
                message = json.message || message;
            } catch (e) { }
            alert(message);
            $("#btn-save-slots").prop("disabled", false);
            $(".btn-save-spinner").hide();
            $(".btn-save-text").show();
        },
        success: function () {
            $(".btn-save-spinner").hide();
            $(".btn-save-text-saved").show();

            $("#slots-success-alert").removeClass("d-none");
            $("html, body").animate({ scrollTop: 0 }, 400);

            setTimeout(function () {
                window.location.href = url("/rdc");
            }, 2000);
        }
    });
}

function cancelSlots() {
    if(confirm("Are you sure you want to discard all slot changes?")) {
        window.location.reload();
    }
}

// =========================================
// View RDC Slots & Panel Modal
// =========================================

function viewRdcSlots(rdcId, rdcName) {
    $("#view-rdc-name").text(rdcName ? `RDC: ${rdcName}` : "RDC Details");
    $("#btn-modal-manage-slots").attr("href", url(`/rdc/slots?id=${rdcId}`));
    
    const content = $("#view-rdc-content");
    content.html(`
        <div class="text-center py-5">
            <div class="spinner-border text-info" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading slots and panel details...</p>
        </div>
    `);
    
    $("#modal-view-rdc").modal("show");
    
    $.ajax({
        method: "GET",
        url: url(`/rdc/api/get-slots?id=${rdcId}`),
        dataType: "json",
        error: function(xhr) {
            let msg = "Failed to load RDC details. Please try again.";
            try {
                const json = JSON.parse(xhr.responseText);
                if (json.message) msg = json.message;
            } catch(e) {}
            content.html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle mr-2"></i> ${msg}
                </div>
            `);
        },
        success: function(res) {
            if (!res.slots || res.slots.length === 0) {
                content.html(`
                    <div class="alert alert-light text-center p-4">
                        <i class="far fa-calendar-times fa-3x text-muted mb-3 d-block"></i>
                        <h5>No slots configured yet</h5>
                        <p class="text-muted">No time slots or panel members have been allocated for this RDC.</p>
                        <a href="${url(`/rdc/slots?id=${rdcId}`)}" class="btn btn-warning btn-sm mt-2">
                            <i class="far fa-clock mr-1"></i> Add Slots Now
                        </a>
                    </div>
                `);
                return;
            }
            
            let html = `<div class="accordion" id="view-slots-accordion">`;
            
            res.slots.forEach(function(slot, idx) {
                const collapseId = `view-slot-collapse-${idx}`;
                const isFirst = idx === 0;
                
                // Internal Guides HTML
                let guidesHtml = '<em class="text-muted">None allocated</em>';
                if (slot.guides && slot.guides.length > 0) {
                    guidesHtml = '<ul class="list-unstyled mb-0">' + slot.guides.map(function(g) {
                        return `<li class="py-1 border-bottom"><i class="fas fa-user-tie text-primary mr-2"></i><strong>${g.name}</strong>${g.designation ? `<br><small class="text-muted ml-4">${g.designation}</small>` : ''}</li>`;
                    }).join('') + '</ul>';
                }
                
                // External Members HTML
                let externalsHtml = '<em class="text-muted">None allocated</em>';
                if (slot.externals && slot.externals.length > 0) {
                    externalsHtml = '<ul class="list-unstyled mb-0">' + slot.externals.map(function(e) {
                        return `<li class="py-1 border-bottom"><i class="fas fa-user-graduate text-success mr-2"></i><strong>${e.name}</strong>${e.organization ? `<br><small class="text-muted ml-4">${e.organization}</small>` : ''}</li>`;
                    }).join('') + '</ul>';
                }
                
                // Students HTML
                let studentsHtml = '<em class="text-muted">None allocated</em>';
                if (slot.students && slot.students.length > 0) {
                    studentsHtml = '<ul class="list-unstyled mb-0">' + slot.students.map(function(s) {
                        return `<li class="py-1 border-bottom d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-user-alt text-info mr-2"></i>${s.name}</span>
                            <span class="badge badge-primary">Turn ${s.turnNumber}</span>
                        </li>`;
                    }).join('') + '</ul>';
                }
                
                html += `
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header bg-light py-2" id="heading-${idx}" style="cursor: pointer;" data-toggle="collapse" data-target="#${collapseId}">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 font-weight-bold text-dark">
                                    <i class="fas fa-clock text-warning mr-2"></i>${slot.name}
                                </h6>
                                <span class="badge badge-secondary py-1 px-2 font-weight-normal">
                                    <i class="far fa-clock mr-1"></i>${slot.fromTime} - ${slot.toTime}
                                </span>
                            </div>
                        </div>
                        <div id="${collapseId}" class="collapse ${isFirst ? 'show' : ''}" data-parent="#view-slots-accordion">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <div class="p-2 border rounded h-100 bg-white">
                                            <h6 class="text-primary font-weight-bold border-bottom pb-2 mb-2">
                                                <i class="fas fa-chalkboard-teacher mr-1"></i> Internal Panel
                                            </h6>
                                            ${guidesHtml}
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <div class="p-2 border rounded h-100 bg-white">
                                            <h6 class="text-success font-weight-bold border-bottom pb-2 mb-2">
                                                <i class="fas fa-university mr-1"></i> External Panel
                                            </h6>
                                            ${externalsHtml}
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-2 border rounded h-100 bg-white">
                                            <h6 class="text-info font-weight-bold border-bottom pb-2 mb-2">
                                                <i class="fas fa-user-graduate mr-1"></i> Students
                                            </h6>
                                            ${studentsHtml}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += `</div>`;
            content.html(html);
        }
    });
}
