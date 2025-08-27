import React, { useState, useMemo } from 'react';
import { Plus, Edit2, Trash2, X, Search, ChevronUp, ChevronDown, BookOpen, Users, Calendar, Hash, FileText, Globe, Building } from 'lucide-react';
import type { Publication } from '../types';

const mockPublications: Publication[] = [
  {
    id: '1',
    name: 'Técnicas Avanzadas de Aprendizaje Automático en Computación Cuántica',
    date: '2024-02-15',
    authors: ['Dr. Juan Pérez', 'Dra. María García', 'Dr. Roberto Martínez'],
    type: 'journal',
    number: '3',
    volume: '15',
    doi: '10.1234/jqc.2024.1234',
    url: 'https://example.com/journal/article'
  },
  {
    id: '2',
    name: 'Sistemas de Almacenamiento de Energía Sostenible',
    date: '2023-11-20',
    authors: ['Dra. Ana López', 'Dr. Carlos Rodríguez'],
    type: 'book',
    publisher: 'Editorial Académica',
    city: 'Madrid'
  },
  {
    id: '3',
    name: 'Redes Neuronales en Modelado Climático',
    date: '2023-08-10',
    authors: ['Dra. Laura Fernández'],
    type: 'book_chapter',
    chapterName: 'Capítulo 5: Aplicaciones de IA en Ciencias Ambientales',
    bookName: 'Inteligencia Artificial para el Cambio Climático',
    bookAuthor: 'Dr. Miguel Torres',
    publisher: 'Editorial MIT'
  }
];

interface PublicationFormData {
  name: string;
  date: string;
  authors: string;
  type: 'journal' | 'book' | 'book_chapter';
  // Journal fields
  number: string;
  volume: string;
  url: string;
  doi: string;
  // Book fields
  publisher: string;
  city: string;
  // Book chapter fields
  chapterName: string;
  bookName: string;
  bookAuthor: string;
}

type SortField = 'name' | 'date' | 'type';
type SortDirection = 'asc' | 'desc';

const FormField = ({ 
  label, 
  icon: Icon, 
  children,
  required = false
}: { 
  label: string; 
  icon: React.ComponentType<{ size: number; className: string }>; 
  children: React.ReactNode;
  required?: boolean;
}) => (
  <div className="space-y-1">
    <label className="block text-sm font-medium text-gray-700">
      <div className="flex items-center gap-2">
        <Icon size={16} className="text-gray-500" />
        {label} {required && <span className="text-red-500">*</span>}
      </div>
    </label>
    {children}
  </div>
);

const PublicationTypeBadge = ({ type }: { type: Publication['type'] }) => {
  const colors = {
    journal: 'bg-blue-100 text-blue-800',
    book: 'bg-green-100 text-green-800',
    book_chapter: 'bg-purple-100 text-purple-800'
  };

  const labels = {
    journal: 'Revista',
    book: 'Libro',
    book_chapter: 'Capítulo de Libro'
  };

  return (
    <span className={`px-2 py-1 rounded-full text-xs font-medium ${colors[type]}`}>
      {labels[type]}
    </span>
  );
};

export const Publications = () => {
  const [publications, setPublications] = useState<Publication[]>(mockPublications);
  const [showForm, setShowForm] = useState(false);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [sortField, setSortField] = useState<SortField>('date');
  const [sortDirection, setSortDirection] = useState<SortDirection>('desc');
  const [typeFilter, setTypeFilter] = useState<string>('all');
  const [yearFilter, setYearFilter] = useState<string>('all');
  const [formData, setFormData] = useState<PublicationFormData>({
    name: '',
    date: '',
    authors: '',
    type: 'journal',
    // Journal fields
    number: '',
    volume: '',
    url: '',
    doi: '',
    // Book fields
    publisher: '',
    city: '',
    // Book chapter fields
    chapterName: '',
    bookName: '',
    bookAuthor: ''
  });

  const publicationTypes = ['all', 'journal', 'book', 'book_chapter'];
  
  const years = useMemo(() => 
    ['all', ...new Set(publications.map(p => new Date(p.date).getFullYear().toString()))].sort((a, b) => b.localeCompare(a)),
    [publications]
  );

  const filteredAndSortedPublications = useMemo(() => {
    return publications
      .filter(publication => {
        const matchesSearch = 
          publication.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
          publication.authors.some(author => 
            author.toLowerCase().includes(searchQuery.toLowerCase())
          );

        const matchesType = typeFilter === 'all' || publication.type === typeFilter;
        const matchesYear = yearFilter === 'all' || new Date(publication.date).getFullYear().toString() === yearFilter;

        return matchesSearch && matchesType && matchesYear;
      })
      .sort((a, b) => {
        let comparison = 0;
        if (sortField === 'date') {
          comparison = new Date(a.date).getTime() - new Date(b.date).getTime();
        } else {
          comparison = a[sortField].localeCompare(b[sortField]);
        }
        return sortDirection === 'asc' ? comparison : -comparison;
      });
  }, [publications, searchQuery, sortField, sortDirection, typeFilter, yearFilter]);

  const handleSort = (field: SortField) => {
    if (sortField === field) {
      setSortDirection(sortDirection === 'asc' ? 'desc' : 'asc');
    } else {
      setSortField(field);
      setSortDirection('asc');
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const publication: Publication = {
      id: editingId || Date.now().toString(),
      name: formData.name,
      date: formData.date,
      authors: formData.authors.split(',').map(author => author.trim()),
      type: formData.type,
      ...(formData.type === 'journal' && {
        number: formData.number,
        volume: formData.volume,
        url: formData.url || undefined,
        doi: formData.doi || undefined
      }),
      ...(formData.type === 'book' && {
        publisher: formData.publisher,
        city: formData.city
      }),
      ...(formData.type === 'book_chapter' && {
        chapterName: formData.chapterName,
        bookName: formData.bookName,
        bookAuthor: formData.bookAuthor,
        publisher: formData.publisher
      })
    };

    if (editingId) {
      setPublications(publications.map(p => p.id === editingId ? publication : p));
    } else {
      setPublications([...publications, publication]);
    }

    setShowForm(false);
    setEditingId(null);
    setFormData({
      name: '',
      date: '',
      authors: '',
      type: 'journal',
      number: '',
      volume: '',
      url: '',
      doi: '',
      publisher: '',
      city: '',
      chapterName: '',
      bookName: '',
      bookAuthor: ''
    });
  };

  const handleEdit = (publication: Publication) => {
    setFormData({
      name: publication.name,
      date: publication.date,
      authors: publication.authors.join(', '),
      type: publication.type,
      number: publication.number || '',
      volume: publication.volume || '',
      url: publication.url || '',
      doi: publication.doi || '',
      publisher: publication.publisher || '',
      city: publication.city || '',
      chapterName: publication.chapterName || '',
      bookName: publication.bookName || '',
      bookAuthor: publication.bookAuthor || ''
    });
    setEditingId(publication.id);
    setShowForm(true);
  };

  const handleDelete = (id: string) => {
    if (confirm('¿Está seguro de que desea eliminar esta publicación?')) {
      setPublications(publications.filter(p => p.id !== id));
    }
  };

  const SortIcon = ({ field }: { field: SortField }) => {
    if (sortField !== field) return null;
    return sortDirection === 'asc' ? <ChevronUp size={16} /> : <ChevronDown size={16} />;
  };

  const renderPublicationDetails = (publication: Publication) => {
    switch (publication.type) {
      case 'journal':
        return (
          <div className="text-xs text-gray-600 space-y-1">
            <div><strong>Número:</strong> {publication.number}</div>
            <div><strong>Volumen:</strong> {publication.volume}</div>
            {publication.doi && (
              <div className="text-blue-600">
                <strong>DOI:</strong> {publication.doi}
              </div>
            )}
            {publication.url && (
              <div className="text-blue-600">
                <strong>URL:</strong> <a href={publication.url} target="_blank" rel="noopener noreferrer">Ver</a>
              </div>
            )}
          </div>
        );
      case 'book':
        return (
          <div className="text-xs text-gray-600 space-y-1">
            <div><strong>Editorial:</strong> {publication.publisher}</div>
            <div><strong>Ciudad:</strong> {publication.city}</div>
          </div>
        );
      case 'book_chapter':
        return (
          <div className="text-xs text-gray-600 space-y-1">
            <div><strong>Capítulo:</strong> {publication.chapterName}</div>
            <div><strong>Libro:</strong> {publication.bookName}</div>
            <div><strong>Autor del Libro:</strong> {publication.bookAuthor}</div>
            <div><strong>Editorial:</strong> {publication.publisher}</div>
          </div>
        );
      default:
        return null;
    }
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h2 className="text-2xl font-bold text-gray-900">Publicaciones Científicas</h2>
        <button
          onClick={() => setShowForm(true)}
          className="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2 hover:bg-blue-700 transition-colors"
        >
          <Plus size={20} />
          Agregar Publicación
        </button>
      </div>

      <div className="mb-6 space-y-4">
        <div className="flex flex-col sm:flex-row flex-wrap gap-4">
          <div className="w-full sm:flex-1 min-w-[200px]">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" size={20} />
              <input
                type="text"
                placeholder="Buscar publicaciones..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
          </div>
          <div className="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
            <select
              value={typeFilter}
              onChange={(e) => setTypeFilter(e.target.value)}
              className="w-full sm:w-auto border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="all">Todos los Tipos</option>
              <option value="journal">Revista</option>
              <option value="book">Libro</option>
              <option value="book_chapter">Capítulo de Libro</option>
            </select>
            <select
              value={yearFilter}
              onChange={(e) => setYearFilter(e.target.value)}
              className="w-full sm:w-auto border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              {years.map(year => (
                <option key={year} value={year}>
                  {year === 'all' ? 'Todos los Años' : year}
                </option>
              ))}
            </select>
          </div>
        </div>
      </div>

      {showForm && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-xl p-4 sm:p-8 max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center mb-6">
              <h3 className="text-xl sm:text-2xl font-bold text-gray-900">
                {editingId ? 'Editar Publicación' : 'Agregar Nueva Publicación'}
              </h3>
              <button
                onClick={() => {
                  setShowForm(false);
                  setEditingId(null);
                }}
                className="text-gray-500 hover:text-gray-700 transition-colors"
              >
                <X size={24} />
              </button>
            </div>
            <form onSubmit={handleSubmit} className="space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <FormField label="Nombre de Publicación" icon={BookOpen} required>
                  <input
                    type="text"
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                    placeholder="Ingrese el nombre de la publicación"
                    required
                  />
                </FormField>

                <FormField label="Fecha" icon={Calendar} required>
                  <input
                    type="date"
                    value={formData.date}
                    onChange={(e) => setFormData({ ...formData, date: e.target.value })}
                    className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                    required
                  />
                </FormField>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <FormField label="Autores (Profesores)" icon={Users} required>
                  <input
                    type="text"
                    value={formData.authors}
                    onChange={(e) => setFormData({ ...formData, authors: e.target.value })}
                    className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                    placeholder="Ingrese los autores (separados por comas)"
                    required
                  />
                </FormField>

                <FormField label="Tipo de Publicación" icon={BookOpen} required>
                  <select
                    value={formData.type}
                    onChange={(e) => setFormData({ ...formData, type: e.target.value as Publication['type'] })}
                    className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                    required
                  >
                    <option value="journal">Revista</option>
                    <option value="book">Libro</option>
                    <option value="book_chapter">Capítulo de Libro</option>
                  </select>
                </FormField>
              </div>

              {/* Journal Fields */}
              {formData.type === 'journal' && (
                <div className="space-y-6 border-t pt-6">
                  <h4 className="text-lg font-semibold text-gray-900">Información de Revista</h4>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <FormField label="Número" icon={Hash} required>
                      <input
                        type="text"
                        value={formData.number}
                        onChange={(e) => setFormData({ ...formData, number: e.target.value })}
                        className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                        placeholder="Número de la revista"
                        required
                      />
                    </FormField>

                    <FormField label="Volumen" icon={Hash} required>
                      <input
                        type="text"
                        value={formData.volume}
                        onChange={(e) => setFormData({ ...formData, volume: e.target.value })}
                        className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                        placeholder="Volumen de la revista"
                        required
                      />
                    </FormField>

                    <FormField label="URL" icon={Globe}>
                      <input
                        type="url"
                        value={formData.url}
                        onChange={(e) => setFormData({ ...formData, url: e.target.value })}
                        className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                        placeholder="https://ejemplo.com/articulo"
                      />
                    </FormField>

                    <FormField label="DOI" icon={Hash}>
                      <input
                        type="text"
                        value={formData.doi}
                        onChange={(e) => setFormData({ ...formData, doi: e.target.value })}
                        className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                        placeholder="10.xxxx/xxxxx"
                      />
                    </FormField>
                  </div>
                </div>
              )}

              {/* Book Fields */}
              {formData.type === 'book' && (
                <div className="space-y-6 border-t pt-6">
                  <h4 className="text-lg font-semibold text-gray-900">Información del Libro</h4>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <FormField label="Editorial" icon={Building} required>
                      <input
                        type="text"
                        value={formData.publisher}
                        onChange={(e) => setFormData({ ...formData, publisher: e.target.value })}
                        className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                        placeholder="Ingrese la editorial"
                        required
                      />
                    </FormField>

                    <FormField label="Ciudad" icon={Building} required>
                      <input
                        type="text"
                        value={formData.city}
                        onChange={(e) => setFormData({ ...formData, city: e.target.value })}
                        className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                        placeholder="Ingrese la ciudad"
                        required
                      />
                    </FormField>
                  </div>
                </div>
              )}

              {/* Book Chapter Fields */}
              {formData.type === 'book_chapter' && (
                <div className="space-y-6 border-t pt-6">
                  <h4 className="text-lg font-semibold text-gray-900">Información del Capítulo de Libro</h4>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <FormField label="Nombre del Libro" icon={BookOpen} required>
                      <input
                        type="text"
                        value={formData.bookName}
                        onChange={(e) => setFormData({ ...formData, bookName: e.target.value })}
                        className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                        placeholder="Ingrese el nombre del libro"
                        required
                      />
                    </FormField>

                    <FormField label="Autor del Libro" icon={Users} required>
                      <input
                        type="text"
                        value={formData.bookAuthor}
                        onChange={(e) => setFormData({ ...formData, bookAuthor: e.target.value })}
                        className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                        placeholder="Ingrese el autor del libro"
                        required
                      />
                    </FormField>

                    <FormField label="Capítulo del Libro" icon={FileText} required>
                      <input
                        type="text"
                        value={formData.chapterName}
                        onChange={(e) => setFormData({ ...formData, chapterName: e.target.value })}
                        className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                        placeholder="Ingrese el nombre del capítulo"
                        required
                      />
                    </FormField>

                    <FormField label="Editorial" icon={Building} required>
                      <input
                        type="text"
                        value={formData.publisher}
                        onChange={(e) => setFormData({ ...formData, publisher: e.target.value })}
                        className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                        placeholder="Ingrese la editorial"
                        required
                      />
                    </FormField>
                  </div>
                </div>
              )}

              <div className="flex flex-col sm:flex-row justify-end gap-4 pt-4">
                <button
                  type="button"
                  onClick={() => {
                    setShowForm(false);
                    setEditingId(null);
                  }}
                  className="w-full sm:w-auto px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  className="w-full sm:w-auto px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                >
                  {editingId ? 'Actualizar Publicación' : 'Guardar Publicación'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      <div className="bg-white shadow-md rounded-lg overflow-hidden">
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th 
                  className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                  onClick={() => handleSort('name')}
                >
                  <div className="flex items-center gap-1">
                    Nombre de Publicación
                    <SortIcon field="name" />
                  </div>
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Autores
                </th>
                <th 
                  className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                  onClick={() => handleSort('type')}
                >
                  <div className="flex items-center gap-1">
                    Tipo
                    <SortIcon field="type" />
                  </div>
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Detalles
                </th>
                <th 
                  className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                  onClick={() => handleSort('date')}
                >
                  <div className="flex items-center gap-1">
                    Fecha
                    <SortIcon field="date" />
                  </div>
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {filteredAndSortedPublications.map((publication) => (
                <tr key={publication.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4">
                    <div className="text-sm font-medium text-gray-900">{publication.name}</div>
                  </td>
                  <td className="px-6 py-4">
                    <div className="text-sm text-gray-900">
                      {publication.authors.join(', ')}
                    </div>
                  </td>
                  <td className="px-6 py-4">
                    <PublicationTypeBadge type={publication.type} />
                  </td>
                  <td className="px-6 py-4">
                    {renderPublicationDetails(publication)}
                  </td>
                  <td className="px-6 py-4">
                    <div className="text-sm text-gray-500">
                      {new Date(publication.date).toLocaleDateString()}
                    </div>
                  </td>
                  <td className="px-6 py-4 text-right text-sm font-medium">
                    <div className="flex justify-end gap-2">
                      <button
                        onClick={() => handleEdit(publication)}
                        className="text-blue-600 hover:text-blue-900"
                      >
                        <Edit2 size={18} />
                      </button>
                      <button
                        onClick={() => handleDelete(publication.id)}
                        className="text-red-600 hover:text-red-900"
                      >
                        <Trash2 size={18} />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};