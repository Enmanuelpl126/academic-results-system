import React, { useState, useMemo } from 'react';
import { Plus, Edit2, Trash2, X, Search, ChevronUp, ChevronDown, Trophy, Building, Calendar, FileText } from 'lucide-react';
import type { Award } from '../types';

const mockAwards: Award[] = [
  {
    id: '1',
    title: 'Best Research Paper Award',
    type: 'research',
    organization: 'International Science Foundation',
    date: '2024-02-15',
    description: 'Awarded for groundbreaking research in quantum computing applications.'
  },
  {
    id: '2',
    title: 'Innovation Excellence Award',
    type: 'innovation',
    organization: 'Tech Innovation Forum',
    date: '2023-11-20',
    description: 'Recognition for innovative solutions in sustainable technology.'
  }
];

interface AwardFormData {
  title: string;
  type: 'research' | 'innovation' | 'academic' | 'industry';
  organization: string;
  date: string;
  description: string;
}

type SortField = 'title' | 'type' | 'date';
type SortDirection = 'asc' | 'desc';

const FormField = ({ 
  label, 
  icon: Icon, 
  children 
}: { 
  label: string; 
  icon: React.ComponentType<{ size: number; className: string }>; 
  children: React.ReactNode 
}) => (
  <div className="space-y-1">
    <label className="block text-sm font-medium text-gray-700">
      <div className="flex items-center gap-2">
        <Icon size={16} className="text-gray-500" />
        {label}
      </div>
    </label>
    {children}
  </div>
);

const AwardTypeBadge = ({ type }: { type: Award['type'] }) => {
  const colors = {
    research: 'bg-purple-100 text-purple-800',
    innovation: 'bg-blue-100 text-blue-800',
    academic: 'bg-green-100 text-green-800',
    industry: 'bg-orange-100 text-orange-800'
  };

  return (
    <span className={`px-2 py-1 rounded-full text-xs font-medium ${colors[type]}`}>
      {type.charAt(0).toUpperCase() + type.slice(1)}
    </span>
  );
};

export const Awards = () => {
  const [awards, setAwards] = useState<Award[]>(mockAwards);
  const [showForm, setShowForm] = useState(false);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [sortField, setSortField] = useState<SortField>('date');
  const [sortDirection, setSortDirection] = useState<SortDirection>('desc');
  const [typeFilter, setTypeFilter] = useState<string>('all');
  const [formData, setFormData] = useState<AwardFormData>({
    title: '',
    type: 'research',
    organization: '',
    date: '',
    description: ''
  });

  const awardTypes = ['all', 'research', 'innovation', 'academic', 'industry'];

  const filteredAndSortedAwards = useMemo(() => {
    return awards
      .filter(award => {
        const matchesSearch = 
          award.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
          award.organization.toLowerCase().includes(searchQuery.toLowerCase()) ||
          award.description.toLowerCase().includes(searchQuery.toLowerCase());

        const matchesType = typeFilter === 'all' || award.type === typeFilter;

        return matchesSearch && matchesType;
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
  }, [awards, searchQuery, sortField, sortDirection, typeFilter]);

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
    const award: Award = {
      id: editingId || Date.now().toString(),
      ...formData
    };

    if (editingId) {
      setAwards(awards.map(a => a.id === editingId ? award : a));
    } else {
      setAwards([...awards, award]);
    }

    setShowForm(false);
    setEditingId(null);
    setFormData({
      title: '',
      type: 'research',
      organization: '',
      date: '',
      description: ''
    });
  };

  const handleEdit = (award: Award) => {
    setFormData({
      ...award
    });
    setEditingId(award.id);
    setShowForm(true);
  };

  const handleDelete = (id: string) => {
    if (confirm('Are you sure you want to delete this award?')) {
      setAwards(awards.filter(a => a.id !== id));
    }
  };

  const SortIcon = ({ field }: { field: SortField }) => {
    if (sortField !== field) return null;
    return sortDirection === 'asc' ? <ChevronUp size={16} /> : <ChevronDown size={16} />;
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h2 className="text-2xl font-bold text-gray-900">Awards</h2>
        <button
          onClick={() => setShowForm(true)}
          className="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2 hover:bg-blue-700 transition-colors"
        >
          <Plus size={20} />
          Add Award
        </button>
      </div>

      <div className="mb-6 space-y-4">
        <div className="flex flex-col sm:flex-row flex-wrap gap-4">
          <div className="w-full sm:flex-1 min-w-[200px]">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" size={20} />
              <input
                type="text"
                placeholder="Search awards..."
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
              {awardTypes.map(type => (
                <option key={type} value={type}>
                  {type === 'all' ? 'All Types' : type.charAt(0).toUpperCase() + type.slice(1)}
                </option>
              ))}
            </select>
          </div>
        </div>
      </div>

      {showForm && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-xl p-4 sm:p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center mb-6">
              <h3 className="text-xl sm:text-2xl font-bold text-gray-900">
                {editingId ? 'Edit Award' : 'Add New Award'}
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
              <FormField label="Award Title" icon={Trophy}>
                <input
                  type="text"
                  value={formData.title}
                  onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                  className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                  placeholder="Enter award title"
                  required
                />
              </FormField>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <FormField label="Award Type" icon={Trophy}>
                  <select
                    value={formData.type}
                    onChange={(e) => setFormData({ ...formData, type: e.target.value as Award['type'] })}
                    className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                    required
                  >
                    <option value="research">Research</option>
                    <option value="innovation">Innovation</option>
                    <option value="academic">Academic</option>
                    <option value="industry">Industry</option>
                  </select>
                </FormField>

                <FormField label="Date" icon={Calendar}>
                  <input
                    type="date"
                    value={formData.date}
                    onChange={(e) => setFormData({ ...formData, date: e.target.value })}
                    className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                    required
                  />
                </FormField>
              </div>

              <FormField label="Organization" icon={Building}>
                <input
                  type="text"
                  value={formData.organization}
                  onChange={(e) => setFormData({ ...formData, organization: e.target.value })}
                  className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                  placeholder="Enter organization name"
                  required
                />
              </FormField>

              <FormField label="Description" icon={FileText}>
                <textarea
                  value={formData.description}
                  onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                  rows={4}
                  className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                  placeholder="Enter award description"
                  required
                />
              </FormField>

              <div className="flex flex-col sm:flex-row justify-end gap-4 pt-4">
                <button
                  type="button"
                  onClick={() => {
                    setShowForm(false);
                    setEditingId(null);
                  }}
                  className="w-full sm:w-auto px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="w-full sm:w-auto px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                >
                  {editingId ? 'Update Award' : 'Save Award'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {filteredAndSortedAwards.map((award) => (
          <div key={award.id} className="bg-white rounded-lg shadow-md p-6">
            <div className="flex justify-between items-start mb-4">
              <div>
                <h3 className="text-lg font-semibold text-gray-900">{award.title}</h3>
                <div className="flex items-center gap-2 text-sm text-gray-600 mt-1">
                  <Building size={16} className="text-gray-400" />
                  {award.organization}
                </div>
                <div className="flex items-center gap-2 text-sm text-gray-500 mt-1">
                  <Calendar size={16} className="text-gray-400" />
                  {new Date(award.date).toLocaleDateString()}
                </div>
              </div>
              <div className="flex gap-2">
                <button
                  onClick={() => handleEdit(award)}
                  className="text-blue-600 hover:text-blue-900"
                >
                  <Edit2 size={18} />
                </button>
                <button
                  onClick={() => handleDelete(award.id)}
                  className="text-red-600 hover:text-red-900"
                >
                  <Trash2 size={18} />
                </button>
              </div>
            </div>
            <div className="mb-3">
              <AwardTypeBadge type={award.type} />
            </div>
            <p className="text-gray-700">{award.description}</p>
          </div>
        ))}
      </div>
    </div>
  );
};