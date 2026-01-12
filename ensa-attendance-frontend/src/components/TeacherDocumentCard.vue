<template>
  <div class="document-card">
    <div class="card-icon">📄</div>
    <div class="card-content">
      <h3 class="card-title">{{ document.titre }}</h3>
      <div class="card-meta">
        <span class="meta-item">
          <span class="meta-label">Type:</span> 
          <span class="meta-value">{{ document.type_document.toUpperCase() }}</span>
        </span>
        <span class="meta-item">
          <span class="meta-label">Ajouté le</span> 
          <span class="meta-value">{{ formatDate(document.date_upload) }}</span>
        </span>
      </div>
      <p class="card-author">
        👤 {{ document.enseignant?.utilisateur?.nom || 'Inconnu' }}
      </p>
    </div>
    <div class="card-actions">
      <a 
        :href="`http://127.0.0.1:8000/api/documents/${document.id}/download`" 
        class="btn-download"
        download
      >
        Télécharger
      </a>
      <button @click="$emit('delete', document.id)" class="btn-delete">
        Supprimer
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps(['document'])
defineEmits(['delete'])

function formatDate(date) {
  return new Date(date).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}
</script>

<style scoped>
.document-card {
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

.document-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
  border-color: #e0e7ff;
}

.card-icon {
  font-size: 3rem;
  text-align: center;
  padding: 25px 20px 15px;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

.card-content {
  padding: 0 20px 15px;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.card-title {
  color: #1e293b;
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
  line-height: 1.4;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
}

.card-meta {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.meta-item {
  font-size: 0.85rem;
  color: #64748b;
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

.meta-label {
  font-weight: 600;
  color: #475569;
}

.meta-value {
  color: #64748b;
}

.card-author {
  font-size: 0.9rem;
  color: #94a3b8;
  margin: 0;
  padding: 8px 12px;
  background: #f8fafc;
  border-radius: 8px;
  font-weight: 500;
}

.card-actions {
  display: flex;
  gap: 8px;
  padding: 15px 20px;
  background: #f8fafc;
  border-top: 1px solid #f1f5f9;
}

.btn-download {
  flex: 1;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  padding: 10px 16px;
  border-radius: 10px;
  text-align: center;
  text-decoration: none;
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  border: none;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
}

.btn-download:hover {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-delete {
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
  .card-actions {
    flex-direction: column;
  }
  
  .btn-download,
  .btn-delete {
    width: 100%;
  }
}
</style>