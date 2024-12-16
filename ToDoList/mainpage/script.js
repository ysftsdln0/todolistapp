document.getElementById('task-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const taskInput = document.getElementById('task-input');
    const priorityInput = document.getElementById('priority-input');
    const taskValue = taskInput.value.trim();
    const priorityValue = parseInt(priorityInput.value.trim());

    if (taskValue && priorityValue) {
        const li = document.createElement('li');
        li.setAttribute('data-priority', priorityValue);
        li.innerHTML = `
            <span>${taskValue}</span>
            <span class="priority"><i class="fas fa-flag"></i> Öncelik: ${priorityValue}</span>
            <button class="delete-btn"><i class="fas fa-trash-alt"></i></button>
        `;

        const deleteBtn = li.querySelector('.delete-btn');
        deleteBtn.onclick = function () {
            li.remove();
        };

        document.getElementById('task-list').appendChild(li);

        // Görevleri öncelik sırasına göre sırala
        sortTasks();

        taskInput.value = '';
        priorityInput.value = '';
    }
});
// Görevleri sıralama fonksiyonu
function sortTasks() {
    const taskList = document.getElementById('task-list');
    const tasks = Array.from(taskList.children);

    tasks.sort((a, b) => a.getAttribute('data-priority') - b.getAttribute('data-priority'));

    tasks.forEach(task => taskList.appendChild(task));
}