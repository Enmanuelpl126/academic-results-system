import React, { useState, useMemo } from 'react';
import { Plus, Edit2, Trash2, X, Calendar, FileText, Search, ChevronUp, ChevronDown, MapPin, UserCircle } from 'lucide-react';
import type { Event } from '../types';

const mockEvents: Event[] = [
  {
    id: '1',
    name: 'International Conference on Quantum Computing',
    type: 'conference',
    role: 'speaker',
    location: 'Berlin, Germany',
    date: '2024-06-15',
    description: 'Presenting research findings on quantum computing applications in machine learning.'
  },
  {
    id: '2',
    name: 'Advanced AI Workshop',
    type: 'workshop',
    role: 'organizer',
    location: 'San Francisco, USA',
    date: '2024-04-20',
    description: 'Leading a workshop on the latest developments in artificial intelligence.'
  }
];

interface EventFormData {
  name: string;
  type: 'conference' | 'workshop' | 'seminar';
  role: 'speaker' | 'attendee' | 'organizer';
  location: string;
  date: string;
  description: string;
}

type SortField = 'name' | 'type' | 'date';
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

const EventTypeIcon = ({ type }: { type: Event['type'] }) => {
  switch (type) {
    case 'conference':
      return <Calendar className="text-blue-500" size={20} />;
    case 'workshop':
      return <UserCircle className="text-green-500" size={20} />;
    case 'seminar':
      return <FileText className="text-purple-500" size={20} />;
  }
};

const EventRoleBadge = ({ role }: { role: Event['role'] }) => {
  const colors = {
    speaker: 'bg-blue-100 text-blue-800',
    attendee: 'bg-gray-100 text-gray-800',
    organizer: 'bg-green-100 text-green-800'
  };

  return (
    <span className={`px-2 py-1 rounded-full text-xs font-medium ${colors[role]}`}>
      {role.charAt(0).toUpperCase() + role.slice(1)}
    </span>
  );
};

export const Events = () => {
  const [events, setEvents] = useState<Event[]>(mockEvents);
  const [showForm, setShowForm] = useState(false);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [sortField, setSortField] = useState<SortField>('date');
  const [sortDirection, setSortDirection] = useState<SortDirection>('desc');
  const [typeFilter, setTypeFilter] = useState<string>('all');
  const [roleFilter, setRoleFilter] = useState<string>('all');
  const [formData, setFormData] = useState<EventFormData>({
    name: '',
    type: 'conference',
    role: 'speaker',
    location: '',
    date: '',
    description: ''
  });

  const eventTypes = ['all', 'conference', 'workshop', 'seminar'];
  const eventRoles = ['all', 'speaker', 'attendee', 'organizer'];

  const filteredAndSortedEvents = useMemo(() => {
    return events
      .filter(event => {
        const matchesSearch = 
          event.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
          event.location.toLowerCase().includes(searchQuery.toLowerCase()) ||
          event.description.toLowerCase().includes(searchQuery.toLowerCase());

        const matchesType = typeFilter === 'all' || event.type === typeFilter;
        const matchesRole = roleFilter === 'all' || event.role === roleFilter;

        return matchesSearch && matchesType && matchesRole;
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
  }, [events, searchQuery, sortField, sortDirection, typeFilter, roleFilter]);

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
    const event: Event = {
      id: editingId || Date.now().toString(),
      ...formData
    };

    if (editingId) {
      setEvents(events.map(e => e.id === editingId ? event : e));
    } else {
      setEvents([...events, event]);
    }

    setShowForm(false);
    setEditingId(null);
    setFormData({
      name: '',
      type: 'conference',
      role: 'speaker',
      location: '',
      date: '',
      description: ''
    });
  };

  const handleEdit = (event: Event) => {
    setFormData({
      ...event
    });
    setEditingId(event.id);
    setShowForm(true);
  };

  const handleDelete = (id: string) => {
    if (confirm('Are you sure you want to delete this event?')) {
      setEvents(events.filter(e => e.id !== id));
    }
  };

  const SortIcon = ({ field }: { field: SortField }) => {
    if (sortField !== field) return null;
    return sortDirection === 'asc' ? <ChevronUp size={16} /> : <ChevronDown size={16} />;
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h2 className="text-2xl font-bold text-gray-900">Events</h2>
        <button
          onClick={() => setShowForm(true)}
          className="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2 hover:bg-blue-700 transition-colors"
        >
          <Plus size={20} />
          Add Event
        </button>
      </div>

      <div className="mb-6 space-y-4">
        <div className="flex flex-col sm:flex-row flex-wrap gap-4">
          <div className="w-full sm:flex-1 min-w-[200px]">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" size={20} />
              <input
                type="text"
                placeholder="Search events..."
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
              {eventTypes.map(type => (
                <option key={type} value={type}>
                  {type === 'all' ? 'All Types' : type.charAt(0).toUpperCase() + type.slice(1)}
                </option>
              ))}
            </select>
            <select
              value={roleFilter}
              onChange={(e) => setRoleFilter(e.target.value)}
              className="w-full sm:w-auto border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              {eventRoles.map(role => (
                <option key={role} value={role}>
                  {role === 'all' ? 'All Roles' : role.charAt(0).toUpperCase() + role.slice(1)}
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
                {editingId ? 'Edit Event' : 'Add New Event'}
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
              <FormField label="Event Name" icon={Calendar}>
                <input
                  type="text"
                  value={formData.name}
                  onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                  className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                  placeholder="Enter event name"
                  required
                />
              </FormField>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <FormField label="Event Type" icon={Calendar}>
                  <select
                    value={formData.type}
                    onChange={(e) => setFormData({ ...formData, type: e.target.value as Event['type'] })}
                    className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                    required
                  >
                    <option value="conference">Conference</option>
                    <option value="workshop">Workshop</option>
                    <option value="seminar">Seminar</option>
                  </select>
                </FormField>

                <FormField label="Your Role" icon={UserCircle}>
                  <select
                    value={formData.role}
                    onChange={(e) => setFormData({ ...formData, role: e.target.value as Event['role'] })}
                    className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                    required
                  >
                    <option value="speaker">Speaker</option>
                    <option value="attendee">Attendee</option>
                    <option value="organizer">Organizer</option>
                  </select>
                </FormField>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <FormField label="Location" icon={MapPin}>
                  <input
                    type="text"
                    value={formData.location}
                    onChange={(e) => setFormData({ ...formData, location: e.target.value })}
                    className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                    placeholder="Enter location"
                    required
                  />
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

              <FormField label="Description" icon={FileText}>
                <textarea
                  value={formData.description}
                  onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                  rows={4}
                  className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                  placeholder="Enter event description"
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
                  {editingId ? 'Update Event' : 'Save Event'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {filteredAndSortedEvents.map((event) => (
          <div key={event.id} className="bg-white rounded-lg shadow-md p-6">
            <div className="flex justify-between items-start mb-4">
              <div className="flex gap-3">
                <EventTypeIcon type={event.type} />
                <div>
                  <h3 className="text-lg font-semibold text-gray-900">{event.name}</h3>
                  <div className="flex items-center gap-2 text-sm text-gray-600 mt-1">
                    <MapPin size={16} className="text-gray-400" />
                    {event.location}
                  </div>
                  <div className="flex items-center gap-2 text-sm text-gray-500 mt-1">
                    <Calendar size={16} className="text-gray-400" />
                    {new Date(event.date).toLocaleDateString()}
                  </div>
                </div>
              </div>
              <div className="flex gap-2">
                <button
                  onClick={() => handleEdit(event)}
                  className="text-blue-600 hover:text-blue-900"
                >
                  <Edit2 size={18} />
                </button>
                <button
                  onClick={() => handleDelete(event.id)}
                  className="text-red-600 hover:text-red-900"
                >
                  <Trash2 size={18} />
                </button>
              </div>
            </div>
            <div className="mb-3">
              <EventRoleBadge role={event.role} />
            </div>
            <p className="text-gray-700">{event.description}</p>
          </div>
        ))}
      </div>
    </div>
  );
};