<template>
  <div class="annonce-card">
    <div class="card-header">
      <div class="header-icon">📢</div>
      <h3 class="card-title">{{ annonce.titre }}</h3>
    </div>
    
    <div class="card-body">
      <p class="card-content">{{ annonce.contenu }}</p>
    </div>
    
    <div class="card-footer">
      <div class="footer-meta">
        <span class="meta-date">📅 {{ formatDate(annonce.date_creation) }}</span>
        <span class="meta-author">👤 {{ annonce.enseignant?.utilisateur?.nom || 'Anonyme' }}</span>
      </div>
      <div class="footer-actions">
        <button @click="$emit('edit', annonce)" class="btn-edit">
          Modifier
        </button>
        <button @click="$emit('delete', annonce.id)" class="btn-delete">
          Supprimer
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps(['annonce'])
defineEmits(['edit', 'delete'])

function formatDate(date) {
  return new Date(date).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}
</script>

<style scoped>
.annonce-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  height: 100%;
  border: 1px solid #f1f5f9;
}

.annonce-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
  border-color: #fef3c7;
}

.card-header {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.header-icon {
  font-size: 2rem;
  line-height: 1;
}

.card-title {
  color: #78350f;
  margin: 0;
  font-size: 1.15rem;
  font-weight: 700;
  line-height: 1.4;
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
}

.card-body {
  padding: 20px;
  flex: 1;
  overflow: hidden;
}

.card-content {
  color: #475569;
  margin: 0;
  line-height: 1.6;
  font-size: 0.95rem;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 6;
  line-clamp: 6;
  -webkit-box-orient: vertical;
  word-wrap: break-word;
}

.card-footer {
  padding: 15px 20px;
  background: #f8fafc;
  border-top: 1px solid #f1f5f9;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.footer-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  font-size: 0.85rem;
  color: #64748b;
}

.meta-date,
.meta-author {
  display: flex;
  align-items: center;
  gap: 4px;
  font-weight: 500;
}

.footer-actions {
  display: flex;
  gap: 8px;
}

.btn-edit {
  flex: 1;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  padding: 10px 16px;
  border-radius: 10px;
  border: none;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
}

.btn-edit:hover {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-delete {
  flex: 1;
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  padding: 10px 16px;
  border-radius: 10px;
  border: none;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
}

.btn-delete:hover {
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

@media (max-width: 768px) {
  .footer-meta {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>