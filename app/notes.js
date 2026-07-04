// Notes App Logic
window.appNotas = {
    notes: [
        { id: 1, title: 'Bienvenido', content: 'Usa esta libreta para organizar tus ideas o anotar requerimientos para tu proyecto.' },
        { id: 2, title: 'Ideas de Automatización', content: '1. Integrar WhatsApp con CRM.\n2. Automatizar facturas en PDF.\n3. Chatbot IA en soporte.' }
    ],
    activeNoteId: 1,
    
    open: function() {
        window.openDockModal('notes-modal');
        this.renderList();
        this.loadActiveNote();
    },
    
    renderList: function() {
        const ul = document.getElementById('notes-list-ul');
        if (!ul) return;
        ul.innerHTML = '';
        this.notes.forEach(note => {
            const li = document.createElement('li');
            li.className = 'notes-list-item' + (note.id === this.activeNoteId ? ' active' : '');
            li.onclick = () => {
                this.activeNoteId = note.id;
                this.renderList();
                this.loadActiveNote();
            };
            
            const firstLine = note.content.split('\n')[0] || '';
            li.innerHTML = `
                <div class="notes-item-title"></div>
                <div class="notes-item-desc"></div>
            `;
            li.querySelector('.notes-item-title').textContent = note.title || 'Sin Título';
            li.querySelector('.notes-item-desc').textContent = firstLine || 'Nota vacía';
            ul.appendChild(li);
        });
    },
    
    loadActiveNote: function() {
        const note = this.notes.find(n => n.id === this.activeNoteId);
        const titleIn = document.getElementById('notes-title-in');
        const textIn = document.getElementById('dock-notes-text');
        if (!note) return;
        if (titleIn) titleIn.value = note.title;
        if (textIn) textIn.value = note.content;
    },
    
    addNote: function() {
        const newId = Date.now();
        this.notes.unshift({
            id: newId,
            title: 'Nueva Nota',
            content: ''
        });
        this.activeNoteId = newId;
        this.renderList();
        this.loadActiveNote();
    },
    
    saveNote: function() {
        const note = this.notes.find(n => n.id === this.activeNoteId);
        if (!note) return;
        const titleIn = document.getElementById('notes-title-in');
        const textIn = document.getElementById('dock-notes-text');
        if (titleIn) note.title = titleIn.value;
        if (textIn) note.content = textIn.value;
        this.renderList();
    }
};

// Add change listeners for autosave
document.addEventListener('DOMContentLoaded', () => {
    const titleIn = document.getElementById('notes-title-in');
    const textIn = document.getElementById('dock-notes-text');
    if (titleIn) titleIn.addEventListener('input', () => window.appNotas.saveNote());
    if (textIn) textIn.addEventListener('input', () => window.appNotas.saveNote());
});
